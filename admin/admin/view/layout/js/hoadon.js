'use strict';
let invoiceParams=new URLSearchParams(),invoicePage=1,invoiceGeneration=0;
const invoiceStatus=['Chờ xác nhận','Đang giao','Đã nhận hàng','Đã hủy'];
const paymentStatus={PENDING:'Chưa thanh toán',PAID:'Đã thanh toán',FAILED:'Thất bại',REFUNDED:'Đã hoàn tiền',REVIEW:'Cần đối soát'};
const refundStatus={QUEUED:'Chờ hoàn tiền',SENDING:'Đang gửi hoàn tiền',PROCESSING:'Đang hoàn tiền',SUCCEEDED:'Đã hoàn tiền',REJECTED:'Hoàn tiền bị từ chối',UNKNOWN:'Hoàn tiền cần đối soát',REVIEW:'Hoàn tiền cần kiểm tra'};
async function loadPage(page){
    invoicePage=page;const generation=++invoiceGeneration;
    const params=new URLSearchParams(invoiceParams);params.set('page',page);
    const message=document.getElementById('after-sales-message');
    try{
        const response=await fetch('../controller/pagHoaDon.php?'+params,{cache:'no-store'});
        if(!response.ok)throw Error('Không tải được đơn hàng.');
        const data=await response.json();if(generation!==invoiceGeneration)return;
        if(data.status==='error')throw Error(data.message);
        invoicePage=Number(data.currentPage);
        const tbody=document.getElementById('invoice-table-body');tbody.replaceChildren();
        for(const invoice of data.data){
            const draft=invoice.row_type==='checkout';
            const row=document.createElement('tr');row.id=draft?'checkout-'+Number(invoice.checkout_id):'invoice-'+Number(invoice.idhoadon);
            const cell=text=>{const td=document.createElement('td');td.textContent=text ?? '';row.append(td);return td;};
            cell(draft?'TT-'+invoice.checkout_id:invoice.idhoadon);cell(invoice.idkhachhang);cell(invoice.thanhpho);cell(invoice.idnhanvien);
            const payment=cell(invoice.phuongthuctt);
            const note=(target,text)=>{if(!text)return;const p=document.createElement('div');p.textContent=text;target.append(p);};
            const cancelledCod=Number(invoice.trangthai)===3 && ['cod','Thanh toán khi nhận hàng'].includes(invoice.phuongthuctt) && invoice.trangthai_thanhtoan==='PENDING';
            note(payment,cancelledCod?'Đã hủy — không thu tiền':paymentStatus[invoice.trangthai_thanhtoan]);
            note(payment,refundStatus[invoice.refund_status]);
            cell(invoice.ngayxuat);cell(Number(invoice.tongtien).toLocaleString('vi-VN')+'đ');
            const state=cell(draft?'Chưa hoàn tất thanh toán':invoiceStatus[Number(invoice.trangthai)] || '');
            if(!draft && [0,1].includes(Number(invoice.trangthai)) && invoice.cancellation_status==='PENDING'){
                const link=document.createElement('button');link.type='button';link.className='cancel-review-link';link.textContent='Đang chờ xét duyệt hủy';link.addEventListener('click',()=>openCancelDecision(invoice));state.append(link);
            }
            const controls=cell('');
            if(!draft){const detail=document.createElement('a');detail.href='../controller/index.php?pg=xetduyetHD&idhoadon='+Number(invoice.idhoadon);detail.textContent='Chi tiết';controls.append(detail);}
            else controls.textContent='Chưa phát hành hóa đơn';
            const button=(label,action)=>{const b=document.createElement('button');b.type='button';b.textContent=label;b.className='order-refund-action';b.addEventListener('click',()=>actInvoice(invoice.idhoadon,action,controls));controls.append(b);};
            if(invoice.refund_status && !['SUCCEEDED','REJECTED'].includes(invoice.refund_status))button(invoice.refund_status==='QUEUED'?'Gửi hoàn tiền':'Kiểm tra hoàn tiền','check-refund');
            tbody.append(row);
        }
        if(!data.data.length){const row=document.createElement('tr'),td=document.createElement('td');td.colSpan=9;td.textContent='Không có đơn hàng phù hợp.';row.append(td);tbody.append(row);}
        const pagination=document.getElementById('pagination_invoice');pagination.replaceChildren();
        for(let i=1;i<=data.totalPages;i++){const b=document.createElement('button');b.textContent=i;b.classList.toggle('active',i===Number(data.currentPage));b.addEventListener('click',()=>loadPage(i));pagination.append(b);}
    }catch(error){message.textContent=error.message;}
}
let refundCheckBusy=false;
async function actInvoice(id,action,controls,note=''){
    const checkingRefund=action==='check-refund';
    const refundMessage=document.getElementById('refund-result-message');
    if(checkingRefund){
        if(refundCheckBusy)return false;
        refundCheckBusy=true;
        document.getElementById('after-sales-message').textContent='';
        document.getElementById('refund-result-title').textContent='Kiểm tra hoàn tiền — Đơn #'+id;
        refundMessage.textContent='Đang kiểm tra kết quả hoàn tiền, vui lòng chờ…';
        const dialog=document.getElementById('refund-result-dialog');if(!dialog.open)dialog.showModal();
    }
    controls.querySelectorAll('button').forEach(b=>b.disabled=true);
    try{
        const data=await (await fetch('../controller/after-sales.php?action='+action,{method:'POST',body:new URLSearchParams({order_id:id,csrf_token:document.getElementById('admin-after-sales').dataset.csrf,note})})).json();
        if(data.status!=='success')throw Error(data.message);
        if(checkingRefund)refundMessage.textContent=data.message;
        else document.getElementById('after-sales-message').textContent=data.message;
        await loadPage(invoicePage);return true;
    }catch(error){
        const text=error.message || 'Chưa nhận được kết quả. Vui lòng kiểm tra lại sau.';
        if(checkingRefund)refundMessage.textContent=text;
        else{document.getElementById('after-sales-message').textContent=text;document.getElementById('cancel-decision-message').textContent=text;}
        return false;
    }
    finally{if(checkingRefund)refundCheckBusy=false;controls.querySelectorAll('button').forEach(b=>b.disabled=false);}
}
let cancelDecisionOrder=0,cancelDecisionBusy=false;
function openCancelDecision(invoice){
    cancelDecisionOrder=Number(invoice.idhoadon);
    document.getElementById('cancel-decision-form').reset();
    document.getElementById('cancel-decision-title').textContent='Xét duyệt hủy đơn #'+cancelDecisionOrder;
    document.getElementById('cancel-customer-reason').textContent=invoice.cancellation_reason || 'Khách chưa ghi lý do.';
    document.getElementById('cancel-decision-message').textContent='';
    document.getElementById('cancel-decision-dialog').showModal();
}
function setupCancelDecision(){
    const dialog=document.getElementById('cancel-decision-dialog'),form=document.getElementById('cancel-decision-form');
    document.getElementById('close-cancel-decision').addEventListener('click',()=>dialog.close());
    dialog.addEventListener('cancel',event=>{if(cancelDecisionBusy)event.preventDefault();});
    form.addEventListener('submit',async event=>{
        event.preventDefault();if(cancelDecisionBusy)return;
        const action=event.submitter?.value,note=document.getElementById('cancel-decision-note').value.trim();
        if(!['approve','reject'].includes(action) || !note){document.getElementById('cancel-decision-message').textContent='Vui lòng nhập lý do và chọn duyệt hoặc từ chối.';return;}
        cancelDecisionBusy=true;
        try{if(await actInvoice(cancelDecisionOrder,action,form,note))dialog.close();}
        finally{cancelDecisionBusy=false;}
    });
}
function searchHoaDon(keyword){invoiceParams=new URLSearchParams();if(keyword)invoiceParams.set('search',keyword);loadPage(1);}
function applyFiltersHD(){
    const start=document.getElementById('startDate').value,end=document.getElementById('endDate').value;
    if(start && end && start>end){alert('Ngày bắt đầu không được lớn hơn ngày kết thúc.');return;}
    invoiceParams=new URLSearchParams();
    for(const [key,id] of [['startDate','startDate'],['endDate','endDate'],['city','filterCity'],['status','filterStatus']]){
        const value=document.getElementById(id).value.trim();if(value!=='')invoiceParams.set(key,value);
    }
    document.getElementById('searchHD').value='';loadPage(1);
}
document.addEventListener('DOMContentLoaded',()=>{
    setupCancelDecision();
    loadPage(1);
    fetch('../view/layout/data/tinh_tp.json').then(r=>r.json()).then(data=>{for(const city of Object.values(data)){const option=document.createElement('option');option.value=city.name_with_type;document.getElementById('cityList').append(option);}}).catch(()=>{});
    document.getElementById('searchHD').addEventListener('input',event=>searchHoaDon(event.target.value.trim()));
});

