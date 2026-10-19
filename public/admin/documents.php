<?php
/**
 * เอกสารเกี่ยวกับเรา — images or PDFs for the About tabs (นโยบาย, เป้าหมาย, คณะทำงาน, มาตรการและแนวปฏิบัติ)
 * and for the complaint handling plan. Upload a file or paste a link.
 */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['editor']);
$pageTitle = 'เอกสารเกี่ยวกับเรา';
$active = 'documents';

$section = array_key_exists($_GET['section'] ?? '', DOC_SECTIONS) ? $_GET['section'] : 'policy';

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($action === 'add') {
            $sec = array_key_exists($_POST['section'] ?? '', DOC_SECTIONS) ? $_POST['section'] : $section;
            $title = trim((string)($_POST['title'] ?? ''));
            if ($title === '') {
                throw new RuntimeException('กรุณาใส่ชื่อเอกสาร');
            }
            $path = save_upload('file', 'docs');
            $link = clean_url((string)($_POST['external_url'] ?? ''));
            if (!$path && !$link) {
                throw new RuntimeException('กรุณาเลือกไฟล์ หรือใส่ลิงก์เอกสาร');
            }
            if ($path) {
                $type = str_ends_with($path, '.pdf') ? 'pdf' : 'image';
            } else {
                $type = preg_match('/\.pdf($|\?)/i', $link) ? 'pdf' : (preg_match('/\.(jpe?g|png|webp)($|\?)/i', $link) ? 'image' : 'link');
            }
            q('INSERT INTO documents (section, title, file_path, external_url, file_type, sort) VALUES (?,?,?,?,?,?)',
                [$sec, mb_substr($title, 0, 255), $path, $path ? null : $link, $type, (int)($_POST['sort'] ?? 0)]);
            flash('เพิ่มเอกสารแล้ว');
            $section = $sec;
        } elseif ($action === 'update') {
            q('UPDATE documents SET title = ?, sort = ?, is_published = ? WHERE id = ?',
                [mb_substr(trim((string)$_POST['title']), 0, 255), (int)$_POST['sort'], isset($_POST['is_published']) ? 1 : 0, $id]);
            flash('บันทึกแล้ว');
        } elseif ($action === 'delete') {
            $d = row('SELECT file_path FROM documents WHERE id = ?', [$id]);
            q('DELETE FROM documents WHERE id = ?', [$id]);
            delete_upload($d['file_path'] ?? null);
            flash('ลบเอกสารแล้ว');
        }
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect('admin/documents.php?section=' . $section);
}

$docs = rows('SELECT * FROM documents WHERE section = ? ORDER BY sort, id', [$section]);
require __DIR__ . '/../../app/views/admin_top.php';
?>
<nav aria-label="หมวดเอกสาร" class="flex gap-2 flex-wrap">
  <?php foreach (DOC_SECTIONS as $k => $label): ?>
    <a href="<?= e(url('admin/documents.php?section=' . $k)) ?>" class="btn border-2 border-ku <?= $k === $section ? 'bg-ku text-white' : 'bg-white text-ku' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<div class="flex flex-wrap gap-4 items-start">
  <form method="post" enctype="multipart/form-data" class="flex-[1_1_320px] bg-white rounded-3xl p-6 flex flex-col gap-3.5">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="section" value="<?= e($section) ?>">
    <h2 class="font-display font-medium text-xl">เพิ่มเอกสาร · <?= e(DOC_SECTIONS[$section]) ?></h2>
    <label class="label">ชื่อเอกสาร<input type="text" name="title" required maxlength="255" class="input"></label>
    <label class="label">ไฟล์ (PDF / JPG / PNG / WEBP)<input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="input py-2.5"></label>
    <div class="text-center text-sm text-muted">— หรือ —</div>
    <label class="label">ลิงก์เอกสาร (เช่น Google Drive)<input type="url" name="external_url" placeholder="https://" class="input"></label>
    <label class="label">ลำดับ<input type="number" name="sort" value="<?= count($docs) + 1 ?>" class="input w-28"></label>
    <button class="btn-primary">อัปโหลด / เพิ่ม</button>
    <p class="text-xs text-muted">ไฟล์ PDF แสดงในหน้าเว็บแบบเปิดอ่านได้ทันที รูปภาพแสดงเต็มกว้าง</p>
  </form>

  <div class="flex-[2_1_480px] flex flex-col gap-3">
    <?php if (!$docs): ?><div class="bg-white rounded-3xl p-6 text-muted">ยังไม่มีเอกสารในหมวดนี้</div><?php endif; ?>
    <?php foreach ($docs as $d): ?>
      <div class="bg-white rounded-3xl p-4 flex flex-wrap gap-3 items-center">
        <span class="w-11 h-11 rounded-xl text-white text-xs font-bold flex items-center justify-center flex-none <?= $d['file_type'] === 'image' ? 'bg-[#155E80]' : ($d['file_type'] === 'pdf' ? 'bg-[#B23A48]' : 'bg-ku') ?>"><?= strtoupper($d['file_type'] === 'image' ? 'IMG' : $d['file_type']) ?></span>
        <form method="post" class="flex-1 min-w-[260px] flex flex-wrap gap-2 items-center">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
          <input type="text" name="title" value="<?= e($d['title']) ?>" aria-label="ชื่อเอกสาร" class="input flex-1 min-w-[180px] w-auto">
          <input type="number" name="sort" value="<?= (int)$d['sort'] ?>" aria-label="ลำดับ" class="input w-20">
          <label class="flex items-center gap-1.5 text-sm"><input type="checkbox" name="is_published" <?= $d['is_published'] ? 'checked' : '' ?> class="w-5 h-5 accent-ku"> แสดง</label>
          <button class="btn-sm btn-outline">บันทึก</button>
        </form>
        <a href="<?= e(doc_href($d)) ?>" target="_blank" rel="noopener" class="btn-sm btn bg-mint text-ku">เปิด</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button data-confirm="ลบเอกสารนี้?" class="btn-sm btn-danger">ลบ</button></form>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
