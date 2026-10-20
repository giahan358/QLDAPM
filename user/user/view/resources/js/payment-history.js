(() => {
    'use strict';
    const root=document.getElementById('user-after-sales'),anchor=document.querySelector('.purchase-history');
    if(!root || !anchor)return;
    const section=document.createElement('section');section.className='ub-product-reviews';
    const heading=document.createElement('h3');heading.textContent='Đơn VNPAY chưa hoàn tất';
    const message=document.createElement('p');message.setAttribute('role','status');
    const rows=document.createElement('div');section.append(heading,message,rows);anchor.closest('.table-scroll').after(section);
    const labels={FAILED:'Thanh toán thất bại',PENDING:'Chờ xác nhận thanh toán',REVIEW:'Cần kiểm tra giao dịch'};
    let loading=false,snapshot=null,reconciling=false;
    const nextChecks=new Map();
    async function reconcilePending(items){
        if(reconciling || document.hidden || navigator.onLine===false)return;
        const item=items.find(row=>row.status==='PENDING' && (nextChecks.get(row.id)||0)<=Date.now());
        if(!item)return;
        reconciling=true;nextChecks.set(item.id,Date.now()+300000);
        try{
            const response=await fetch('../controller/thanhtoan.php',{method:'POST',body:new URLSearchParams({action:'check-payment',checkout_id:item.id,csrf_token:root.dataset.csrf})});
            const data=await response.json();
            nextChecks.set(item.id,Date.now()+Math.max(30,Number(data.retry_after)||30)*1000);
            if(data.status==='success' && !data.retry_after)window.dispatchEvent(new Event('unibook:orders-changed'));
        }catch(_){nextChecks.set(item.id,Date.now()+60000);}
        finally{reconciling=false;}
    }
    async function load(){
        if(loading)return;
        loading=true;
        try{
            const result=await (await fetch('../controller/payment-history.php',{cache:'no-store'})).json();
            if(result.status!=='success')throw Error(result.message);
            reconcilePending(result.data);
            const nextSnapshot=JSON.stringify(result.data);if(nextSnapshot===snapshot)return;snapshot=nextSnapshot;
            rows.replaceChildren();section.hidden=!result.data.length;
            for(const item of result.data){
                const expired=item.status==='PENDING' && Number(item.expired)===1;
                const row=document.createElement('div');row.className='ub-review-card';
                const text=document.createElement('p');text.textContent='Đơn TT-'+item.id+' · '+Number(item.amount).toLocaleString('vi-VN')+'đ · '+item.quantity+' sản phẩm · '+(expired?'Liên kết hết hạn — chưa xác nhận thanh toán':(labels[item.status] || item.status))+' · Lần thanh toán '+item.attempt_no;
                row.append(text);
                if(expired){
                    const note=document.createElement('p');note.textContent='Nếu chưa bị trừ tiền, bạn có thể tạo đơn mới. Nếu đã bị trừ tiền, hãy kiểm tra kết quả hoặc liên hệ cửa hàng trước khi thanh toán thêm.';row.append(note);
                    const shop=document.createElement('a');shop.href='index.php';shop.textContent='Tiếp tục mua sắm';shop.style.marginRight='12px';row.append(shop);
                }
                if(item.status==='PENDING'){
                    const check=document.createElement('button');check.type='button';check.textContent='Kiểm tra kết quả';
                    check.addEventListener('click',async()=>{
                        check.disabled=true;
                        try{const data=await (await fetch('../controller/thanhtoan.php',{method:'POST',body:new URLSearchParams({action:'check-payment',checkout_id:item.id,csrf_token:root.dataset.csrf})})).json();message.textContent=data.message;window.dispatchEvent(new Event('unibook:orders-changed'));}
                        catch(error){message.textContent='Không kiểm tra được kết quả. Vui lòng thử lại sau.';}
                        finally{check.disabled=false;}
                    });row.append(check);
                }
                if(item.status==='FAILED' && Number(item.attempt_no)<6 || (item.status==='PENDING' && !expired)){
                    const button=document.createElement('button');button.type='button';button.textContent=item.status==='FAILED'?'Thanh toán lại':'Tiếp tục thanh toán';
                    button.addEventListener('click',async()=>{
                        button.disabled=true;message.textContent='Đang tạo liên kết thanh toán…';
                        try{
                            const response=await fetch('../controller/thanhtoan.php',{method:'POST',body:new URLSearchParams({action:'retry',checkout_id:item.id,csrf_token:root.dataset.csrf})});
                            const data=await response.json();if(data.status!=='success')throw Error(data.message);
                            if(data.redirectUrl){window.location.assign(data.redirectUrl);return;}
                            message.textContent='Đơn đã thanh toán thành công.';window.dispatchEvent(new Event('unibook:orders-changed'));
                        }catch(error){message.textContent=error.message || 'Không thể thanh toán lại. Vui lòng thử lại.';}
                        finally{button.disabled=false;}
                    });row.append(button);
                }else if(item.status==='FAILED'){const note=document.createElement('p');note.textContent='Đã hết 5 lần thanh toán lại. Vui lòng liên hệ cửa hàng.';row.append(note);}
                rows.append(row);
            }
        }catch(error){message.textContent=error.message || 'Không tải được lịch sử thanh toán.';}
        finally{loading=false;}
    }
    window.addEventListener('unibook:orders-changed',load);load();
})();
