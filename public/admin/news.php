<?php
/** จัดการเนื้อหา — news and activities shown on the website. */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['editor']);
$pageTitle = 'จัดการเนื้อหา (ข่าว)';
$active = 'news';
$cats = ['กิจกรรม', 'ข่าวสาร', 'ประกาศ'];
$statuses = ['draft' => ['ฉบับร่าง', '#EDF0EA', '#3E5553'], 'review' => ['รอตรวจ', '#FFF2C2', '#6B4E00'], 'published' => ['เผยแพร่แล้ว', '#DDF2E6', '#1E6B45']];

if (is_post()) {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    try {
        if (($_POST['action'] ?? '') === 'delete') {
            $n = row('SELECT image_path FROM news WHERE id = ?', [$id]);
            q('DELETE FROM news WHERE id = ?', [$id]);
            delete_upload($n['image_path'] ?? null);
            flash('ลบข่าวแล้ว');
            redirect('admin/news.php');
        }
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') {
            throw new RuntimeException('กรุณาใส่หัวข้อข่าว');
        }
        $cat = in_array($_POST['category'] ?? '', $cats, true) ? $_POST['category'] : 'ข่าวสาร';
        $status = array_key_exists($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'draft';
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['published_at'] ?? '')) ? $_POST['published_at'] : date('Y-m-d');
        $img = save_upload('image', 'news', ['jpg', 'jpeg', 'png', 'webp']);
        $data = [$cat, mb_substr($title, 0, 255), mb_substr(trim((string)$_POST['summary']), 0, 500), trim((string)$_POST['body']), $status, $date];
        if ($id) {
            if ($img) {
                delete_upload(val('SELECT image_path FROM news WHERE id = ?', [$id]) ?: null);
                q('UPDATE news SET image_path = ? WHERE id = ?', [$img, $id]);
            }
            q('UPDATE news SET category = ?, title = ?, summary = ?, body = ?, status = ?, published_at = ? WHERE id = ?', [...$data, $id]);
        } else {
            q('INSERT INTO news (category, title, summary, body, status, published_at, image_path) VALUES (?,?,?,?,?,?,?)', [...$data, $img]);
        }
        flash('บันทึกข่าวแล้ว');
        redirect('admin/news.php');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
        redirect('admin/news.php' . ($id ? '?edit=' . $id : '?new=1'));
    }
}

$editing = isset($_GET['new']) ? ['id' => 0, 'category' => 'ข่าวสาร', 'title' => '', 'summary' => '', 'body' => '', 'status' => 'draft', 'published_at' => date('Y-m-d'), 'image_path' => null]
    : (isset($_GET['edit']) ? row('SELECT * FROM news WHERE id = ?', [(int)$_GET['edit']]) : null);
$list = rows('SELECT id, category, title, status, published_at FROM news ORDER BY published_at DESC, id DESC');

require __DIR__ . '/../../app/views/admin_top.php';
?>
<?php if ($editing): ?>
<form method="post" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 flex flex-col gap-4 max-w-4xl">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
  <h2 class="font-display font-medium text-xl"><?= $editing['id'] ? 'แก้ไขข่าว' : 'เพิ่มข่าวใหม่' ?></h2>
  <label class="label">หัวข้อ<input type="text" name="title" value="<?= e($editing['title']) ?>" required maxlength="255" class="input"></label>
  <div class="grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(180px,1fr))]">
    <label class="label">ประเภท<select name="category" class="input"><?php foreach ($cats as $c): ?><option <?= $editing['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></label>
    <label class="label">สถานะ<select name="status" class="input"><?php foreach ($statuses as $k => [$l]): ?><option value="<?= $k ?>" <?= $editing['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
    <label class="label">วันที่เผยแพร่<input type="date" name="published_at" value="<?= e($editing['published_at']) ?>" class="input"></label>
  </div>
  <label class="label">สรุปสั้น (แสดงบนการ์ด)<input type="text" name="summary" value="<?= e($editing['summary']) ?>" maxlength="500" class="input"></label>
  <label class="label">เนื้อหา<textarea name="body" rows="10" class="input leading-relaxed"><?= e($editing['body']) ?></textarea></label>
  <label class="label">รูปภาพหน้าปก (JPG / PNG / WEBP)<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="input py-2.5"></label>
  <?php if (!empty($editing['image_path'])): ?><img src="<?= e(upload_url($editing['image_path'])) ?>" alt="" class="w-48 rounded-xl"><?php endif; ?>
  <div class="flex gap-2.5"><button class="btn-primary">บันทึก</button><a href="<?= e(url('admin/news.php')) ?>" class="btn-outline">ยกเลิก</a></div>
</form>
<?php else: ?>
<div class="bg-white rounded-3xl p-6 flex flex-col gap-4">
  <div class="flex justify-between items-center gap-3 flex-wrap">
    <h2 class="font-display font-medium text-xl">ข่าวและกิจกรรมทั้งหมด</h2>
    <a href="<?= e(url('admin/news.php?new=1')) ?>" class="btn-primary">+ เพิ่มข่าว</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[640px]">
      <thead><tr class="text-left text-sm text-muted"><th class="p-2.5">หัวข้อ</th><th class="p-2.5">ประเภท</th><th class="p-2.5">วันที่</th><th class="p-2.5">สถานะ</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($list as $n): [$sl, $sb, $sf] = $statuses[$n['status']]; ?>
          <tr class="border-t border-[#EDF0EA] hover:bg-[#F4F9F8]">
            <td class="p-2.5 font-medium"><?= e($n['title']) ?></td><td class="p-2.5"><?= e($n['category']) ?></td><td class="p-2.5 text-sm text-muted"><?= e(thai_date($n['published_at'])) ?></td>
            <td class="p-2.5"><span class="chip" style="background:<?= e($sb) ?>;color:<?= e($sf) ?>"><?= e($sl) ?></span></td>
            <td class="p-2.5 text-right whitespace-nowrap">
              <a href="<?= e(url('admin/news.php?edit=' . $n['id'])) ?>" class="btn-sm btn-outline">แก้ไข</a>
              <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><button data-confirm="ลบข่าวนี้?" class="btn-sm btn-danger">ลบ</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
