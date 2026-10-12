(() => {
    'use strict';
    const dialog=document.getElementById('admin-product-reviews'),list=document.getElementById('admin-product-review-list'),summary=document.getElementById('admin-reviews-summary'),more=document.getElementById('more-product-reviews');
    if(!dialog)return;
    let book=0,page=0,generation=0,loading=false;
    async function load(next){
        const requestGeneration=generation;loading=true;more.disabled=true;
        try{
            const result=await (await fetch('../controller/product-reviews.php?book_id='+book+'&page='+next,{cache:'no-store'})).json();
            if(requestGeneration!==generation)return;
            if(result.status!=='success')throw Error(result.message);
            page=next;summary.textContent=result.total?result.total+' đánh giá · Trung bình '+result.average+'/5 sao':'Sản phẩm chưa có đánh giá.';
            for(const item of result.data){
                const card=document.createElement('article');
                const title=document.createElement('strong');title.textContent='★'.repeat(Number(item.rating))+' · '+item.customer_name;
                const comment=document.createElement('p');comment.textContent=item.comment;
                const link=document.createElement('a');link.textContent='Xem hóa đơn #'+item.order_id;link.href='../controller/index.php?pg=xetduyetHD&idhoadon='+Number(item.order_id);link.target='_blank';link.rel='noopener';
                const date=document.createElement('p');date.textContent=item.created_at;
                card.append(title,comment,link,date);
                const photos=document.createElement('div');photos.className='review-photos';
                for(const filename of item.images){const a=document.createElement('a');a.href='../../../user/uploads/reviews/'+encodeURIComponent(filename);a.target='_blank';a.rel='noopener';const img=document.createElement('img');img.src=a.href;img.alt='Ảnh sản phẩm đã nhận';img.loading='lazy';a.append(img);photos.append(a);}
                card.append(photos);list.append(card);
            }
            more.hidden=page*10>=result.total;
        }catch(error){if(requestGeneration===generation){summary.textContent=error.message || 'Không tải được đánh giá.';more.hidden=false;}}
        finally{if(requestGeneration===generation){loading=false;more.disabled=false;}}
    }
    document.addEventListener('click',event=>{
        const button=event.target.closest('[data-product-reviews]');if(!button)return;
        book=Number(button.dataset.productReviews);page=0;generation++;list.replaceChildren();more.hidden=true;summary.textContent='Đang tải đánh giá…';
        document.getElementById('admin-reviews-title').textContent='Đánh giá sản phẩm #'+book;
        if(!dialog.open)dialog.showModal();dialog.scrollTop=0;load(1);
    });
    more.addEventListener('click',()=>{if(!loading)load(page+1);});
    document.getElementById('close-product-reviews').addEventListener('click',()=>dialog.close());
    dialog.addEventListener('close',()=>{generation++;loading=false;});
})();
