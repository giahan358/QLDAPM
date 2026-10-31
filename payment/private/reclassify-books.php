<?php
declare(strict_types=1);
// One-off catalogue maintenance. CLI only; default is a read-only preview.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/user/model/connectDB.php';
$db=connectdb();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$plan=[
 2=>['Rừng Na Uy','Lãng mạn','Tiểu thuyết tình cảm, mất mát và trưởng thành.'],
 4=>['Chí Phèo','Truyện ngắn','Truyện ngắn hiện thực của Nam Cao.'],
 10=>['Góc sân và khoảng trời','Thơ','Tập thơ thiếu nhi; ưu tiên hình thức thơ cho trường thể loại duy nhất.'],
 15=>['Cô gái đến từ hôm qua','Lãng mạn','Truyện tình cảm học trò.'],
 16=>['Mắt biếc','Lãng mạn','Truyện dài tình yêu đơn phương.'],
 21=>['Tiếng chim hót trong bụi mận gai','Lãng mạn','Tiểu thuyết tình yêu và gia đình.'],
 23=>['Cuốn theo chiều gió','Lãng mạn','Tiểu thuyết tình cảm lịch sử; không phải sách nghiên cứu lịch sử.'],
 24=>['Harry Potter và Hòn đá phù thủy','Kỳ ảo','Fantasy phép thuật.'],
 25=>['Harry Potter và Phòng chứa bí mật','Kỳ ảo','Fantasy phép thuật.'],
 26=>['Harry Potter và tên tù nhân ngục Azkaban','Kỳ ảo','Fantasy phép thuật.'],
 27=>['Harry Potter và chiếc cốc lửa','Kỳ ảo','Fantasy phép thuật.'],
 28=>['Harry Potter và Hội Phượng Hoàng','Kỳ ảo','Fantasy phép thuật.'],
 29=>['Harry Potter và Hoàng tử lai','Kỳ ảo','Fantasy phép thuật.'],
 30=>['Harry Potter và Bảo bối Tử thần','Kỳ ảo','Fantasy phép thuật.'],
 35=>['Đồi gió hú','Lãng mạn','Tiểu thuyết Gothic, tình yêu bi kịch.'],
 36=>['Jane Eyre','Lãng mạn','Tiểu thuyết trưởng thành và tình cảm Gothic.'],
 44=>['Trại súc vật','Tiểu thuyết','Tiểu thuyết ngắn/ngụ ngôn châm biếm chính trị, không phải khoa học viễn tưởng.'],
 46=>['Chúa tể những chiếc nhẫn','Kỳ ảo','Sử thi fantasy Trung Địa.'],
 47=>['Hobbit','Kỳ ảo','Phiêu lưu fantasy Trung Địa.'],
 51=>['Điều kỳ diệu','Thiếu nhi','Wonder: tiểu thuyết dành cho độc giả tuổi thiếu nhi/thiếu niên.'],
];
$apply=in_array('--apply',$argv,true);
$db->beginTransaction();
try {
 $books=$db->query('SELECT * FROM sach ORDER BY idsach FOR UPDATE')->fetchAll(PDO::FETCH_ASSOC);
 $genres=$db->query('SELECT * FROM theloai ORDER BY idtheloai FOR UPDATE')->fetchAll(PDO::FETCH_ASSOC);
 $byId=array_column($books,null,'idsach');$genreNames=array_column($genres,'tentheloai','idtheloai');
 $changes=[];
 foreach($plan as $id=>[$title,$genre,$reason]){
  if(!isset($byId[$id]) || $byId[$id]['tensach']!==$title)throw new RuntimeException('Title changed; review book '.$id.' before applying.');
  if(($genreNames[$byId[$id]['idtheloai']] ?? null)===$genre)continue;
  $changes[]=['id'=>$id,'title'=>$title,'old_id'=>$byId[$id]['idtheloai'],'old_genre'=>$genreNames[$byId[$id]['idtheloai']] ?? null,'new_genre'=>$genre,'reason'=>$reason];
 }
 if(!$apply){$db->rollBack();echo json_encode(['changes'=>$changes,'unresolved'=>[39,59]],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;exit;}
 if(!$changes){$db->commit();echo "No changes needed.\n";exit;}
 $dir=__DIR__.'/backups/genres-'.date('Ymd-His').'-'.bin2hex(random_bytes(3));
 if(!mkdir($dir,0700,true))throw new RuntimeException('Cannot create backup directory.');
 $backup=json_encode(['books'=>$books,'genres'=>$genres],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
 if(file_put_contents($dir.'/before.json',$backup)===false)throw new RuntimeException('Cannot save backup.');
 $ids=array_column($genres,'idtheloai','tentheloai');$added=[];
 foreach($changes as &$change){
  $name=$change['new_genre'];
  if(!isset($ids[$name])){$db->prepare('INSERT INTO theloai(tentheloai,trangthai) VALUES(?,1)')->execute([$name]);$ids[$name]=(int)$db->lastInsertId();$added[]=$ids[$name];}
  $change['new_id']=(int)$ids[$name];
  $q=$db->prepare('UPDATE sach SET idtheloai=? WHERE idsach=? AND tensach=? AND idtheloai <=> ?');
  $q->execute([$change['new_id'],$change['id'],$change['title'],$change['old_id']]);
  if($q->rowCount()!==1)throw new RuntimeException('Concurrent change detected.');
 }unset($change);
 // Only the genre field may change. Verify every other column in every book.
 $after=$db->query('SELECT * FROM sach ORDER BY idsach')->fetchAll(PDO::FETCH_ASSOC);
 $expected=$books;
 foreach($expected as &$book)foreach($changes as $change)if((int)$book['idsach']===$change['id'])$book['idtheloai']=$change['new_id'];unset($book);
 if($after!=$expected)throw new RuntimeException('Unexpected catalogue mutation; rolled back.');
 $restore="-- Restore only this batch's genre assignments, with guards against later changes.\nSTART TRANSACTION;\n";
 foreach($changes as $c)$restore.='UPDATE sach SET idtheloai='.($c['old_id']===null?'NULL':(int)$c['old_id']).' WHERE idsach='.$c['id'].' AND tensach='.$db->quote($c['title']).' AND idtheloai='.$c['new_id'].";\n";
 foreach($added as $id)$restore.="DELETE FROM theloai WHERE idtheloai=$id AND NOT EXISTS (SELECT 1 FROM sach WHERE idtheloai=$id);\n";
 $restore.="COMMIT;\n";
 if(file_put_contents($dir.'/restore.sql',$restore)===false || file_put_contents($dir.'/changes.json',json_encode($changes,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR))===false)throw new RuntimeException('Cannot write audit files.');
 $db->commit();echo json_encode(['updated'=>count($changes),'added_genres'=>array_intersect_key($ids,array_flip(['Kỳ ảo','Truyện ngắn','Thơ'])),'backup'=>$dir,'unresolved'=>[39,59]],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
} catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
