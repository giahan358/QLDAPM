const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawn } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'unibook-address-'));
const edge = spawn('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', [
  '--headless=new', '--disable-gpu', '--no-first-run', '--remote-debugging-port=0',
  '--user-data-dir=' + profile, 'about:blank'
], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
let socket;
(async () => {
  const endpoint = await new Promise((resolve, reject) => {
    let stderr = '';
    const timer = setTimeout(() => reject(Error('Browser startup timed out')), 20000);
    edge.stderr.on('data', chunk => { stderr += chunk; const match = stderr.match(/DevTools listening on (ws:\/\/[^\s]+)/); if (match) { clearTimeout(timer); resolve(match[1]); } });
    edge.on('error', reject);
  });
  const origin = new URL(endpoint).origin.replace('ws:', 'http:');
  const target = await fetch(origin + '/json/new?about:blank', { method: 'PUT' }).then(r => r.json());
  socket = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
  let sequence = 0; const pending = new Map();
  socket.addEventListener('message', event => { const message = JSON.parse(event.data); if (pending.has(message.id)) { const {resolve,reject} = pending.get(message.id); pending.delete(message.id); message.error ? reject(Error(JSON.stringify(message.error))) : resolve(message.result); } });
  const send = (method, params = {}) => new Promise((resolve, reject) => { const id = ++sequence; pending.set(id, {resolve,reject}); socket.send(JSON.stringify({ id, method, params })); });
  const evaluate = async expression => { const r = await send('Runtime.evaluate', {expression,awaitPromise:true,returnByValue:true}); if(r.exceptionDetails) throw Error(JSON.stringify(r.exceptionDetails)); return r.result.value; };
  await send('Page.enable');
  // Fixture reuses the origin of the read-only Return page, whose CSP intentionally blocks fetch.
  await send('Page.setBypassCSP',{enabled:true});
  const loaded = new Promise(resolve => {
    const handler = event => { if (JSON.parse(event.data).method === 'Page.loadEventFired') { socket.removeEventListener('message',handler); resolve(); } };
    socket.addEventListener('message',handler);
  });
  await send('Page.navigate',{url:'http://localhost/qlpm/payment/vnpay-return'});
  await loaded;
  const frame = (await send('Page.getFrameTree')).frameTree.frame.id;

  const html=fs.readFileSync(path.join(root,'user/user/view/after-sales-dialogs.php'),'utf8').replace(/<\?(?:php|=)[\s\S]*?\?>/g,'').replace(/<script[\s\S]*?<\/script>/g,'');
  await send('Page.setDocumentContent',{frameId:frame,html:'<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><base href="http://localhost/qlpm/user/user/controller/"><link rel="stylesheet" href="../view/resources/style/style.css"><link rel="stylesheet" href="../view/resources/style/responsive.css"><body class="mrtung storefront-wide">'+html+'</body>'});
  await evaluate('Promise.all([...document.querySelectorAll("link")].map(l=>l.sheet?Promise.resolve():new Promise(r=>{l.onload=r;l.onerror=r})))');
  for(const width of [375,390,1440]){
    await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
    for(const id of ['cancel-order-dialog','review-order-dialog']){
      const bounds=await evaluate(`(()=>{const d=document.getElementById('${id}');d.showModal();const r=d.getBoundingClientRect();const result={x:r.x,y:r.y,w:r.width,h:r.height,vw:innerWidth,vh:innerHeight};d.close();return result})()`);
      if(Math.abs(bounds.x+bounds.w/2-bounds.vw/2)>2 || Math.abs(bounds.y+bounds.h/2-bounds.vh/2)>2 || bounds.x<0 || bounds.y<0)throw Error('Dialog not centered: '+id+' '+JSON.stringify(bounds));
    }
  }
  console.log('PASS both dialogs centered at widths 375, 390, 1440.');
})().catch(error => { console.error(error); process.exitCode=1; }).finally(() => { if(socket) socket.close(); edge.kill(); });
