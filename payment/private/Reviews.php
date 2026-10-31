<?php
declare(strict_types=1);

final class Reviews {
    private PDO $db;
    public function __construct(PDO $db){$this->db=$db;$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->exec("SET time_zone='+07:00'");}
    private function q(string $sql,array $args=[]):PDOStatement{$q=$this->db->prepare($sql);$q->execute($args);return $q;}
    public function products(int $order,int $customer):array {
        $q=$this->q('SELECT idhoadon,received_at,DATE_ADD(received_at,INTERVAL 10 DAY) AS deadline,(trangthai=2 AND received_at IS NOT NULL AND NOW()>=received_at AND NOW()<DATE_ADD(received_at,INTERVAL 10 DAY)) AS eligible FROM hoadon WHERE idhoadon=? AND idkhachhang=?',[$order,$customer]);
        $o=$q->fetch(PDO::FETCH_ASSOC);if(!$o)throw new DomainException('Không tìm thấy đơn hàng của bạn.');
        $items=$this->q('SELECT DISTINCT s.idsach,s.tensach,r.id AS review_id FROM chitiethoadon d JOIN sach s ON s.idsach=d.idsach LEFT JOIN product_reviews r ON r.order_id=d.idhoadon AND r.book_id=d.idsach WHERE d.idhoadon=? ORDER BY s.idsach',[$order])->fetchAll(PDO::FETCH_ASSOC);
        return ['eligible'=>(bool)$o['eligible'],'deadline'=>$o['deadline'],'products'=>$items];
    }
    /** $uploads comes only from PHP $_FILES; filenames are never supplied by the user. */
    public function create(int $customer,int $order,int $book,int $rating,string $comment,array $uploads=[]):int {
        $comment=trim($comment);
        if($rating<1 || $rating>5 || $comment==='' || mb_strlen($comment)>2000)throw new DomainException('Chọn 1–5 sao và nhập bình luận từ 1 đến 2.000 ký tự.');
        $saved=[];$this->db->beginTransaction();
        try{
            $o=$this->q('SELECT *, (NOW()>=received_at AND NOW()<DATE_ADD(received_at,INTERVAL 10 DAY)) AS in_window FROM hoadon WHERE idhoadon=? FOR UPDATE',[$order])->fetch(PDO::FETCH_ASSOC);
            if(!$o || (int)$o['idkhachhang']!==$customer)throw new DomainException('Không tìm thấy đơn hàng của bạn.');
            if((int)$o['trangthai']!==2 || !$o['received_at'] || !(bool)$o['in_window'])throw new DomainException('Chỉ được đánh giá trong 10 ngày kể từ khi nhận hàng.');
            if(!$this->q('SELECT idchitiethoadon FROM chitiethoadon WHERE idhoadon=? AND idsach=? LIMIT 1',[$order,$book])->fetchColumn())throw new DomainException('Sản phẩm không thuộc đơn hàng này.');
            if($this->q('SELECT id FROM product_reviews WHERE order_id=? AND book_id=?',[$order,$book])->fetchColumn())throw new DomainException('Bạn đã đánh giá sản phẩm này trong đơn hàng.');
            $files=[];
            if(isset($uploads['error'])) {
                if(!is_array($uploads['error']) || !isset($uploads['tmp_name']) || !is_array($uploads['tmp_name']))throw new DomainException('Dữ liệu ảnh không hợp lệ.');
                foreach($uploads['error'] as $i=>$error){
                    if($error===UPLOAD_ERR_NO_FILE)continue;
                    if($error!==UPLOAD_ERR_OK)throw new DomainException('Ảnh tải lên bị lỗi hoặc vượt giới hạn 2 MB.');
                    $tmp=$uploads['tmp_name'][$i] ?? '';
                    if(!is_string($tmp) || !is_uploaded_file($tmp) || filesize($tmp)>2*1024*1024)throw new DomainException('Mỗi ảnh phải nhỏ hơn hoặc bằng 2 MB.');
                    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
                    $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                    $size=@getimagesize($tmp);
                    if(!isset($types[$mime]) || !$size || ($size['mime'] ?? '')!==$mime || $size[0]<1 || $size[1]<1 || $size[0]*$size[1]>12000000)throw new DomainException('Chỉ chấp nhận ảnh JPG, PNG, WebP tối đa 12 megapixel.');
                    $files[]=[$tmp,$types[$mime]];
                }
            }
            if(count($files)>3)throw new DomainException('Mỗi đánh giá tối đa 3 ảnh.');
            $this->q('INSERT INTO product_reviews(order_id,customer_id,book_id,rating,comment,created_at) VALUES(?,?,?,?,?,NOW())',[$order,$customer,$book,$rating,$comment]);
            $id=(int)$this->db->lastInsertId();
            $directory=dirname(__DIR__,2).'/user/uploads/reviews/';
            foreach($files as [$tmp,$extension]){
                $name=bin2hex(random_bytes(20)).'.'.$extension;$path=$directory.$name;
                if(!move_uploaded_file($tmp,$path))throw new RuntimeException('Cannot save review image');
                $saved[]=$path;$this->q('INSERT INTO product_review_images(review_id,filename) VALUES(?,?)',[$id,$name]);
            }
            $this->db->commit();return $id;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();foreach($saved as $path){if(is_file($path))unlink($path);}throw $e;}
    }
    public function listing(int $book,int $page):array {
        $page=max(1,min(10000,$page));$offset=($page-1)*10;
        $summary=$this->q('SELECT COUNT(*) AS total,ROUND(AVG(rating),1) AS average FROM product_reviews WHERE book_id=?',[$book])->fetch(PDO::FETCH_ASSOC);
        $rows=$this->q("SELECT r.id,r.rating,r.comment,r.created_at,k.ten AS customer_name FROM product_reviews r JOIN khachhang k ON k.idkhachhang=r.customer_id WHERE r.book_id=? ORDER BY r.created_at DESC,r.id DESC LIMIT 10 OFFSET $offset",[$book])->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as &$row){$row['images']=$this->q('SELECT filename FROM product_review_images WHERE review_id=? ORDER BY id',[$row['id']])->fetchAll(PDO::FETCH_COLUMN);}
        return ['total'=>(int)$summary['total'],'average'=>$summary['average']===null?0:(float)$summary['average'],'page'=>$page,'reviews'=>$rows];
    }
}
