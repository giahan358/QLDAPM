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
  let html = fs.readFileSync(path.join(root, 'user/user/view/thanh-toan.php'), 'utf8').split('<script src="https://code.jquery')[0].replace(/<\?php[\s\S]*?\?>/g, '');
  await send('Page.setDocumentContent', {frameId:frame,html:'<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><base href="http://localhost/qlpm/user/user/controller/"><link rel="stylesheet" href="../view/resources/style/style.css"><link rel="stylesheet" href="../view/resources/style/responsive.css"><body class="mrtung storefront-wide">'+html+'</body>'});
  await evaluate(fs.readFileSync(path.join(root,'user/user/view/resources/js/shipping-address.js'),'utf8'));
  const results = await evaluate(`(async () => {
    const check = (condition, message) => { if(!condition) throw Error(message); };
    document.getElementById('add-address').style.display='block';
    await UniBookAddress.load('', 'Thành phố Hà Nội', '', 'Phường Ba Đình');
    const city=document.getElementById('city'), ward=document.getElementById('ward');
    check(city.options.length===35, 'Missing provinces');
    check(city.value==='Thành phố Hà Nội', 'Province UTF-8 damaged');
    check(ward.value==='Phường Ba Đình', 'Ward UTF-8 damaged');
    city.value='Tỉnh Cao Bằng'; city.dispatchEvent(new Event('change'));
    check(ward.value==='' && ward.options.length>1, 'Ward not reset on province change');
    city.value=''; city.dispatchEvent(new Event('change'));
    check(ward.disabled && ward.options.length===1, 'Empty province must clear wards');
    await UniBookAddress.load('1','Hồ Chí Minh','Quận 1','Phường Bến Nghé');
    check(document.getElementById('city1').value==='Hồ Chí Minh' && document.getElementById('ward1').value==='Phường Bến Nghé','Legacy address lost');
    await UniBookAddress.load('', 'Thành phố Hà Nội', '', 'Phường Ba Đình');
    return {provinces:city.options.length-1,province:city.value,ward:ward.value,legacyPreserved:true};
  })()`);
  for (const width of [375, 390, 1440]) {
    await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
    const bounds = await evaluate(`(() => { const r=document.getElementById('city').getBoundingClientRect(); return {left:r.left,right:r.right,width:innerWidth}; })()`);
    if(bounds.left < 0 || bounds.right > width) throw Error('Address select overflows at '+width+': '+JSON.stringify(bounds));
  }
  console.log('PASS address browser: '+JSON.stringify(results)+'; widths 375, 390, 1440');
})().catch(error => { console.error(error); process.exitCode=1; }).finally(() => { if(socket) socket.close(); edge.kill(); });
