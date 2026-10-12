(() => {
    const root=document.getElementById('admin-after-sales');if(!root)return;
    const rows=document.getElementById('after-sales-rows'),message=document.getElementById('after-sales-message');
    const cancelLabels={PENDING:'Chờ duyệt',APPROVED:'Đã duyệt',REJECTED:'Từ chối'};
    const refunds={QUEUED:'Chờ gửi hoàn tiền',SENDING:'Đang gửi',PROCESSING:'VNPAY đang xử lý',SUCCEEDED:'Đã hoàn tiền',REJECTED:'VNPAY từ chối',UNKNOWN:'Cần đối soát',REVIEW:'Cần kiểm tra'};
    async function load(){
        try{
            const response=await fetch('../controller/after-sales.php?action=list',{cache:'no-store'});const result=await response.json();
            if(result.status!=='success')throw Error(result.message);
            rows.replaceChildren();
            for(const item of result.data){
                const tr=document.createElement('tr');
                const cell=text=>{const td=document.createElement('td');td.textContent=text;tr.append(td);return td;};
                const link=document.createElement('a');link.href='index.php?pg=xetduyetHD&idhoadon='+Number(item.order_id);link.textContent='#'+item.order_id;cell('').append(link);
                cell(cancelLabels[item.status] || item.status);cell(item.reason+(item.decision_note?' — '+item.decision_note:''));
                cell((refunds[item.refund_status] || 'Không cần hoàn tiền')+(item.refund_amount?' · '+Number(item.refund_amount).toLocaleString('vi-VN')+'đ':'')+(item.response_code?' · mã '+item.response_code+'/'+item.transaction_status:''));
                const controls=cell('');
                function button(text,action){const b=document.createElement('button');b.type='button';b.textContent=text;b.addEventListener('click',()=>act(item.order_id,action,controls));controls.append(b);}
                if(item.status==='PENDING'){button('Duyệt hủy','approve');button('Từ chối','reject');}
                if(item.refund_status && !['SUCCEEDED','REJECTED'].includes(item.refund_status))button(item.refund_status==='QUEUED'?'Gửi hoàn tiền':'Kiểm tra hoàn tiền','check-refund');
                rows.append(tr);
            }
            if(!result.data.length){const tr=document.createElement('tr');const td=document.createElement('td');td.colSpan=5;td.textContent='Chưa có yêu cầu hủy đơn.';tr.append(td);rows.append(tr);}
        }catch(error){message.textContent=error.message || 'Không tải được yêu cầu.';}
    }
    async function act(id,action,controls){
        let note='';
        if(action!=='check-refund'){
            note=prompt(action==='approve'?'Duyệt hủy sẽ hủy đơn, hoàn kho nếu đã trừ và gửi hoàn tiền VNPAY nếu đã thanh toán. Nhập ghi chú:':'Lý do từ chối hủy đơn:','');
            if(note===null)return;
        }
        controls.querySelectorAll('button').forEach(b=>b.disabled=true);
        try{
            const response=await fetch('../controller/after-sales.php?action='+action,{method:'POST',body:new URLSearchParams({order_id:id,csrf_token:root.dataset.csrf,note})});
            const result=await response.json();message.textContent=result.message;await load();
        }catch(error){message.textContent='Chưa nhận được kết quả. Tải lại bảng để kiểm tra trước khi thử lại.';}
        finally{controls.querySelectorAll('button').forEach(b=>b.disabled=false);}
    }
    document.getElementById('refresh-after-sales').addEventListener('click',load);load();
})();
