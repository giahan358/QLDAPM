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
  const frame=(await send('Page.getFrameTree')).frameTree.frame.id;
  const html='<div id="cart-items-container"><div class="item-cart-detail" data-idgiohang="1" data-idsach="1" data-price="10000" data-sltonkho="10"><div class="cart-detail__quanity-col"><input class="-quantity-input" value="2"><button class="increase-btn">+</button><button class="decrease-btn">-</button></div><div class="box-total-price-cart-detail"><p>20000</p></div></div></div><span id="total-price">20000</span><div class="continue-order-page"><button><a href="#checkout">Checkout</a></button></div>';
  await send('Page.setDocumentContent',{frameId:frame,html});
  await evaluate('window.requests=[];window.failNext=false;window.fetch=async url=>{const quantity=Number(new URL(url,"http://localhost").searchParams.get("soLuong"));requests.push(quantity);await new Promise(r=>setTimeout(r,120));const fail=window.failNext;window.failNext=false;return {ok:true,json:async()=>({status:fail?"error":"success",message:"fixture rejection"})}};window.alert=()=>{}');
  await evaluate(fs.readFileSync(path.join(root,'user/user/view/resources/js/cart-quantity.js'),'utf8'));
  const type=async value=>evaluate('(()=>{const i=document.querySelector("input");i.focus();i.value='+JSON.stringify(value)+';i.dispatchEvent(new Event("input",{bubbles:true}));return document.getElementById("total-price").textContent})()');
  const wait=ms=>new Promise(r=>setTimeout(r,ms));
  if(await type('4')!=='40000')throw Error('Typed quantity must update total immediately');
  await wait(280);await type('7');await wait(750);
  const requests=await evaluate('requests');if(requests[requests.length-1]!==7)throw Error('Latest typed quantity not saved');
  await type('99');await wait(450);if(await evaluate('document.querySelector("input").value')!=='10')throw Error('Stock limit not enforced');
  await type('');if(!await evaluate('document.querySelector(".continue-order-page button").disabled'))throw Error('Invalid input should block checkout');
  await evaluate('document.querySelector("input").dispatchEvent(new Event("change",{bubbles:true}))');await wait(180);
  await evaluate('document.querySelector(".decrease-btn").click()');await wait(400);
  if(await evaluate('document.getElementById("total-price").textContent')!=='90000')throw Error('Minus total incorrect');
  await evaluate('window.failNext=true');await type('5');await wait(500);
  if(await evaluate('document.querySelector("input").value')!=='9')throw Error('Rejected update should restore saved quantity');
  console.log('PASS: immediate typed totals, serialized latest quantity, stock clamp, invalid input, minus button, failed save rollback.');
})().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{if(socket)socket.close();edge.kill();});

