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


  const panel=fs.readFileSync(path.join(root,'admin/admin/view/after-sales-panel.php'),'utf8').replace(/<\?(?:php|=)[\s\S]*?\?>/g,'');
  const html=fs.readFileSync(path.join(root,'admin/admin/view/product-reviews-dialog.php'),'utf8').replace(/<script[\s\S]*?<\/script>/g,'');
  await send('Page.setDocumentContent',{frameId:frame,html:'<!doctype html><meta charset="utf-8"><body>'+panel+'<input id="searchHD"><input id="startDate"><input id="endDate"><input id="filterCity"><select id="filterStatus"><option value=""></option></select><datalist id="cityList"></datalist><table><tbody id="invoice-table-body"></tbody></table><div id="pagination_invoice"></div><button data-product-reviews="1">Đánh giá</button>'+html+'</body>'});
  await evaluate(`window.fetch=async function(url,options){
    const review={rating:5,customer_name:'Test',comment:'<img src=x onerror=alert(1)>',order_id:42,created_at:'2026-09-14',images:[]};
    if(String(url).includes('product-reviews.php'))return {json:async()=>({status:'success',total:11,average:5,data:Array.from({length:String(url).includes('page=2')?1:10},()=>review)})};
    if(options?.method==='POST'){window.testAction=String(url);window.testNote=options.body.get('note');return {json:async()=>({status:'success',message:'Đã duyệt'})};}
    return {ok:true,json:async()=>({data:[{idhoadon:42,trangthai:1,cancellation_status:'PENDING',cancellation_reason:'Không cần nữa',tongtien:10000}],totalPages:2,currentPage:1})};
  };window.prompt=()=> 'Test approval';`);
  await evaluate(fs.readFileSync(path.join(root,'admin/admin/view/layout/js/hoadon.js'),'utf8'));
  await evaluate('loadPage(1)');
  await evaluate('setupCancelDecision()');
  const controls=await evaluate(`(()=>{const r=document.querySelector('#invoice-table-body tr');return {text:r.textContent,buttons:[...r.querySelectorAll('button')].map(b=>b.textContent)}})()`);
  if(controls.buttons.includes('Duyệt hủy') || !controls.buttons.includes('Đang chờ xét duyệt hủy') || controls.text.includes('Không cần nữa'))throw Error('Cancellation must be compact');
  await evaluate("document.querySelector('.cancel-review-link').click()");
  const cancel=await evaluate(`(()=>{const link=document.querySelector('.cancel-review-link');return {open:document.getElementById('cancel-decision-dialog').open,reason:document.getElementById('cancel-customer-reason').textContent,color:getComputedStyle(link).color,line:getComputedStyle(link).textDecorationLine}})()`);
  if(!cancel.open || cancel.reason!=='Không cần nữa' || cancel.color!=='rgb(180, 35, 24)' || !cancel.line.includes('underline'))throw Error(JSON.stringify(cancel));
  await evaluate("document.getElementById('cancel-decision-note').value='Đồng ý theo yêu cầu';document.getElementById('cancel-decision-form').requestSubmit(document.querySelector('[value=approve]'))");
  await evaluate('new Promise(r=>setTimeout(r,100))');
  if(!(await evaluate('window.testAction')).includes('action=approve') || await evaluate('window.testNote')!=='Đồng ý theo yêu cầu')throw Error('Approval note missing');
  if(await evaluate("document.getElementById('cancel-decision-dialog').open"))throw Error('Decision dialog not closed');
  await evaluate("openCancelDecision({idhoadon:42,cancellation_reason:'Lý do khác'});document.getElementById('cancel-decision-note').value='Đơn không đủ điều kiện';document.getElementById('cancel-decision-form').requestSubmit(document.querySelector('[value=reject]'))");
  await evaluate('new Promise(r=>setTimeout(r,100))');
  if(!(await evaluate('window.testAction')).includes('action=reject'))throw Error('Reject action missing');
  await evaluate(fs.readFileSync(path.join(root,'admin/admin/view/layout/js/product-reviews.js'),'utf8'));
  await evaluate("document.querySelector('[data-product-reviews]').click()");
  await evaluate('new Promise(r=>setTimeout(r,100))');
  const result=await evaluate(`(()=>{const d=document.getElementById('admin-product-reviews');return {open:d.open,count:d.querySelectorAll('article').length,link:d.querySelector('article a').getAttribute('href'),overflow:d.scrollHeight>d.clientHeight,injected:!!d.querySelector('article p img')};})()`);
  if(!result.open || result.count!==10 || !result.link.includes('idhoadon=42') || !result.overflow || result.injected)throw Error(JSON.stringify(result));
  await evaluate("document.getElementById('more-product-reviews').click()");
  await evaluate('new Promise(r=>setTimeout(r,100))');
  if(await evaluate("document.querySelectorAll('#admin-product-reviews article').length")!==11)throw Error('Review pagination missing');
  await evaluate("document.getElementById('close-product-reviews').click()");
  if(await evaluate("document.getElementById('admin-product-reviews').open"))throw Error('Dialog does not close');
  console.log('PASS red underlined cancellation link, approve/reject dialog with reasons, review scrolling, invoice link, pagination and close.');
})().catch(error => { console.error(error); process.exitCode=1; }).finally(() => { if(socket) socket.close(); edge.kill(); });
