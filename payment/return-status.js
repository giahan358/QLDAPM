(() => {
    'use strict';
    const status=document.getElementById('storefront-status');if(!status)return;
    let busy=false,finished=false,timer=null,nextQuery=0;
    const messages={confirmed:'UniBook đã xác nhận thanh toán thành công.',failed:'UniBook ghi nhận thanh toán thất bại. Sản phẩm vẫn được giữ trong giỏ.',review:'Giao dịch cần cửa hàng kiểm tra. Vui lòng liên hệ hỗ trợ, không thanh toán lại.',not_found:'Không tìm thấy giao dịch trong UniBook. Vui lòng liên hệ cửa hàng và cung cấp mã giao dịch.'};
    async function check(){
        if(busy || finished || document.hidden)return;
        busy=true;clearTimeout(timer);
        try{
            if(Date.now()>=nextQuery){
                nextQuery=Date.now()+30000;
                try{
                    const result=await (await fetch('reconcile-payment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({return_query:location.search.slice(1)})})).json();
                    nextQuery=Date.now()+Math.max(5,Number(result.retry_after)||30)*1000;
                }catch(_){}
            }
            const response=await fetch('storefront-status.php'+location.search,{cache:'no-store'});
            if(response.ok){
                const result=await response.json();
                if(messages[result.status]){
                    finished=true;status.textContent=messages[result.status];
                    if(result.status==='failed'){const link=document.createElement('a');link.href='../user/user/controller/index.php?pg=donhang';link.textContent='Thanh toán lại đơn này';link.style.cssText='display:block;margin-top:16px';status.append(link);}
                    return;
                }
            }
            status.textContent='UniBook đang xác nhận kết quả với VNPAY và sẽ tự cập nhật đơn hàng. Nếu đã thanh toán, bạn không cần thanh toán lại.';
        }catch(_){status.textContent='Kết nối đang gián đoạn. UniBook sẽ tự kiểm tra lại kết quả thanh toán.';}
        finally{busy=false;if(!finished && !document.hidden)timer=setTimeout(check,5000);}
    }
    document.addEventListener('visibilitychange',()=>{clearTimeout(timer);if(!document.hidden)check();});
    window.addEventListener('online',check);
    window.addEventListener('pagehide',()=>clearTimeout(timer));
    window.addEventListener('pageshow',check);
    check();
})();
