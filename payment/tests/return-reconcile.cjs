const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict'),path=require('node:path');
const window=new EventTarget(),document=new EventTarget();let now=1000,queries=0,paid=false;
const status={textContent:'',append(){}};document.hidden=false;document.getElementById=()=>status;
const timers=new Map();let sequence=0;
const context=vm.createContext({window,document,location:{search:'?signed-test'},Date:{now:()=>now},console,
 setTimeout:fn=>{timers.set(++sequence,fn);return sequence;},clearTimeout:id=>timers.delete(id),
 fetch:async url=>{if(url==='reconcile-payment.php'){queries++;if(queries>1)paid=true;return {json:async()=>({status:paid?'processed':'pending',retry_after:paid?0:300})};}return {ok:true,json:async()=>({status:paid?'confirmed':'pending'})};}});
const flush=()=>new Promise(resolve=>setImmediate(resolve));
(async()=>{
 vm.runInContext(fs.readFileSync(path.join(__dirname,'../return-status.js'),'utf8'),context);await flush();assert.equal(queries,1);
 now+=5000;for(const fn of [...timers.values()])fn();await flush();assert.equal(queries,1,'Read polling must not bypass provider query interval');
 now+=300000;for(const fn of [...timers.values()])fn();await flush();assert.equal(queries,2);assert.match(status.textContent,/thành công/);assert.equal(timers.size,0);
 console.log('PASS Return retries after query interval and stops once confirmed.');
})().catch(e=>{console.error(e);process.exitCode=1;});
