const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict'),path=require('node:path');
const root=path.resolve(__dirname,'../..');
const window=new EventTarget(),document=new EventTarget();document.hidden=false;document.getElementById=()=>({});
let now=10000,nextTimer=0,requests=0,status=0,emptyCalls=0,html='',unauthorized=false;
const timers=new Map(),navigator={onLine:true};
function chain(selector){let proxy;proxy=new Proxy({}, {get(_,key){return (...args)=>{
 if(key==='ready'){args[0]();return proxy;}
 if(selector==='#purchase-history-body'){
  if(key==='empty'){emptyCalls++;html='';}
  if(key==='append' && typeof args[0]==='string')html+=args[0];
  if(key==='html' && args.length)html=args[0];
 }
 return proxy;
 };}});return proxy;}
const $=selector=>chain(selector);
$.ajax=options=>{
 requests++;
 if(unauthorized){options.error?.({status:401});options.complete?.();return;}
 options.success({status:'success',data:[{idhoadon:1,ngayxuat:'2026-09-14',tongtien:10000,trangthai:status,cancel_status:'NONE',can_review:status===2?1:0}]});options.complete?.();
};
const context=vm.createContext({window,document,navigator,$,Event,CustomEvent:Event,Date:class extends Date{static now(){return now;}},console,alert:()=>{},setInterval:fn=>{timers.set(++nextTimer,fn);return nextTimer;},clearInterval:id=>timers.delete(id)});
const source=fs.readFileSync(path.join(root,'user/user/view/lichsu-muahang.php'),'utf8');
const inline=source.match(/<script>\s*([\s\S]*?)<\/script>/)[1].replace(/<\?(?:php|=)[\s\S]*?\?>/g,'1');
vm.runInContext(inline,context);assert.match(html,/Chờ xác nhận/);
vm.runInContext(fs.readFileSync(path.join(root,'user/user/view/resources/js/order-sync.js'),'utf8'),context);
function tick(){now+=5000;for(const callback of [...timers.values()])callback();}
status=1;tick();assert.match(html,/Đang giao/);assert.equal(requests,2);
const rendered=emptyCalls;tick();assert.equal(emptyCalls,rendered,'Unchanged response must not redraw');
document.hidden=true;document.dispatchEvent(new Event('visibilitychange'));const before=requests;tick();assert.equal(requests,before);
status=2;document.hidden=false;document.dispatchEvent(new Event('visibilitychange'));assert.match(html,/Đã nhận hàng/);
const focused=requests;window.dispatchEvent(new Event('focus'));assert.equal(requests,focused,'Focus + visibility must not double request');
navigator.onLine=false;window.dispatchEvent(new Event('offline'));tick();assert.equal(requests,focused);
navigator.onLine=true;window.dispatchEvent(new Event('online'));assert.equal(requests,focused+1);
unauthorized=true;tick();assert.match(html,/đăng nhập đã hết hạn/);const ended=requests;tick();assert.equal(requests,ended);assert.equal(timers.size,0);
console.log('PASS live order rendering without reload; unchanged snapshot, hidden/offline pause, resume, focus deduplication, auth expiry.');
