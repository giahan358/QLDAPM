<dialog id="admin-product-reviews" aria-labelledby="admin-reviews-title">
    <header><h2 id="admin-reviews-title">Đánh giá sản phẩm</h2><button type="button" id="close-product-reviews" aria-label="Đóng đánh giá">Đóng</button></header>
    <p id="admin-reviews-summary" role="status"></p>
    <div id="admin-product-review-list"></div>
    <button type="button" id="more-product-reviews" hidden>Xem thêm đánh giá</button>
</dialog>
<style>
dialog#admin-product-reviews {position:fixed;inset:0;margin:auto;transform:none;width:min(720px,calc(100vw - 32px));height:fit-content;max-height:85vh;max-height:85dvh;overflow:auto;box-sizing:border-box;border:0;border-radius:12px;padding:24px;background:#fff;color:#222;}
#admin-product-reviews::backdrop {background:#0008;}
#admin-product-reviews header {display:flex;align-items:center;justify-content:space-between;gap:16px;position:static;}
#admin-product-reviews article {border-top:1px solid #ddd;padding:16px 0;overflow-wrap:anywhere;}
#admin-product-reviews article p {white-space:pre-wrap;}
#admin-product-reviews .review-photos {display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}
#admin-product-reviews img {width:90px;height:90px;object-fit:cover;border-radius:6px;}
</style>
<script src="../view/layout/js/product-reviews.js?v=1" defer></script>
