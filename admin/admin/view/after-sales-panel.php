<?php if(empty($_SESSION['order_admin_csrf']))$_SESSION['order_admin_csrf']=bin2hex(random_bytes(32)); ?>
<div id="admin-after-sales" data-csrf="<?=htmlspecialchars($_SESSION['order_admin_csrf'],ENT_QUOTES,'UTF-8')?>">
    <p id="after-sales-message" role="status"></p>
</div>
<dialog id="cancel-decision-dialog" aria-labelledby="cancel-decision-title">
    <form id="cancel-decision-form">
        <h2 id="cancel-decision-title">Xét duyệt hủy đơn</h2>
        <p><b>Lý do khách yêu cầu hủy:</b></p>
        <p id="cancel-customer-reason"></p>
        <label for="cancel-decision-note">Lý do duyệt hoặc từ chối</label>
        <textarea id="cancel-decision-note" name="note" maxlength="500" rows="4" required></textarea>
        <p id="cancel-decision-message" role="status"></p>
        <div class="cancel-decision-actions">
            <button type="button" id="close-cancel-decision">Đóng</button>
            <button type="submit" name="decision" value="reject">Từ chối hủy</button>
            <button type="submit" name="decision" value="approve">Duyệt hủy</button>
        </div>
    </form>
</dialog>
<dialog id="refund-result-dialog" aria-labelledby="refund-result-title">
    <h2 id="refund-result-title">Kiểm tra hoàn tiền</h2>
    <p id="refund-result-message" role="status"></p>
    <form method="dialog" class="cancel-decision-actions"><button type="submit">Đóng</button></form>
</dialog>
<style>
button.cancel-review-link {display:block;margin:6px auto;padding:0;border:0;background:none;color:#b42318;text-decoration:underline;text-underline-offset:3px;font:inherit;font-weight:600;cursor:pointer;text-align:center;}
#invoice-table-body .order-refund-action {display:block;margin:6px auto;text-align:center;}
dialog#cancel-decision-dialog,dialog#refund-result-dialog {position:fixed;inset:0;margin:auto;transform:none;width:min(560px,calc(100vw - 32px));height:fit-content;max-height:85vh;max-height:85dvh;overflow:auto;box-sizing:border-box;border:0;border-radius:12px;padding:24px;background:#fff;color:#222;}
#cancel-decision-dialog::backdrop,#refund-result-dialog::backdrop {background:#0008;}
#refund-result-message {margin-top:16px;line-height:1.6;white-space:pre-wrap;overflow-wrap:anywhere;}
#cancel-customer-reason {white-space:pre-wrap;overflow-wrap:anywhere;background:#fff3f1;padding:12px;border-radius:6px;}
#cancel-decision-dialog textarea {box-sizing:border-box;width:100%;margin-top:8px;padding:10px;font:inherit;border:1px solid #ccc;border-radius:6px;}
.cancel-decision-actions {display:flex;justify-content:flex-end;flex-wrap:wrap;gap:10px;margin-top:16px;}
#cancel-decision-message {color:#b42318;}
</style>
