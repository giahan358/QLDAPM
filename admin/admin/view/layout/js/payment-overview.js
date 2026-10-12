(() => {
    'use strict';
    const payments=document.getElementById('admin-payment-rows');if(!payments)return;
    const labels={PENDING:'Chờ xác nhận',PAID:'Đã thanh toán',FAILED:'Thất bại',REVIEW:'Cần đối soát'};
    const refunds={QUEUED:'Chờ hoàn tiền',SENDING:'Đang gửi hoàn tiền',PROCESSING:'Đang hoàn tiền',SUCCEEDED:'Đã hoàn tiền',REJECTED:'Hoàn tiền bị từ chối',UNKNOWN:'Hoàn tiền cần đối soát',REVIEW:'Hoàn tiền cần kiểm tra'};
    async function load(){
        try{
            const result=await (await fetch('../controller/after-sales.php?action=overview',{cache:'no-store'})).json();if(result.status!=='success')throw Error(result.message);
            payments.replaceChildren();
            for(const item of result.payments){
                const tr=document.createElement('tr');
                for(const text of ['TT-'+item.id+(item.invoice_id?' / #'+item.invoice_id:''),'#'+item.customer_id,Number(item.amount).toLocaleString('vi-VN')+'đ',(labels[item.status] || item.status)+(item.response_code?' · mã '+item.response_code+'/'+item.transaction_status:'')+(refunds[item.refund_status]?' · '+refunds[item.refund_status]:''),'Lần '+item.attempt_no+' · '+item.txn_ref]){const td=document.createElement('td');td.textContent=text;tr.append(td);}
                payments.append(tr);
            }
            if(!result.payments.length){const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=5;td.textContent='Chưa có giao dịch VNPAY.';tr.append(td);payments.append(tr);}
        }catch(error){document.getElementById('after-sales-message').textContent=error.message || 'Không tải được giao dịch.';}
    }
    document.getElementById('refresh-after-sales').addEventListener('click',load);
    document.getElementById('admin-payment-overview').addEventListener('toggle',event=>{if(event.target.open)load();});
})();
