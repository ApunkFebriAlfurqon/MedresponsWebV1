<?php
require_once __DIR__ . '/../controllers/ProgramController.php';

$controller = new ProgramController();
$viewData = $controller->handle();
$programs = $viewData['programs'];
$formProgram = $viewData['formProgram'];
$isEditing = $viewData['isEditing'];
$flash = $viewData['flash'];
$csrfToken = $viewData['csrfToken'];
$validationErrors = $viewData['validationErrors'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Program — RapidAid Admin</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <?php if ($flash): ?>
        <div class="alert alert-<?=clean($flash['type'])?> mb-16" data-auto-dismiss><?=clean($flash['message'])?></div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3 class="card-title"><?=$isEditing ? 'Edit Program' : 'Tambah Program'?></h3>
        </div>
        <form method="POST" action="programs.php">
          <input type="hidden" name="_csrf" value="<?=clean($csrfToken)?>"/>
          <input type="hidden" name="action" value="<?=$isEditing ? 'update' : 'create'?>"/>
          <?php if ($isEditing): ?>
            <input type="hidden" name="id" value="<?=$formProgram->id?>"/>
          <?php endif; ?>
          <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div class="form-group">
              <label class="form-label" for="nama">Nama Program *</label>
              <input class="form-control" id="nama" name="nama" type="text" maxlength="100" required aria-invalid="<?=isset($validationErrors['nama']) ? 'true' : 'false'?>" aria-describedby="nama-error" value="<?=clean($formProgram->nama)?>" placeholder="Contoh: Pendidikan"/>
              <?php if (isset($validationErrors['nama'])): ?>
                <small id="nama-error" style="display:block;color:var(--red);margin-top:6px;" role="alert"><?=clean($validationErrors['nama'])?></small>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label class="form-label" for="deskripsi">Deskripsi *</label>
              <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required aria-invalid="<?=isset($validationErrors['deskripsi']) ? 'true' : 'false'?>" aria-describedby="deskripsi-error" placeholder="Jelaskan tujuan program"><?=clean($formProgram->deskripsi ?? '')?></textarea>
              <?php if (isset($validationErrors['deskripsi'])): ?>
                <small id="deskripsi-error" style="display:block;color:var(--red);margin-top:6px;" role="alert"><?=clean($validationErrors['deskripsi'])?></small>
              <?php endif; ?>
            </div>
          </div>
          <div style="padding:0 24px 24px;display:flex;justify-content:flex-end;gap:8px;">
            <?php if ($isEditing): ?>
              <a class="btn btn-ghost" href="programs.php">Batal</a>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit"><?=$isEditing ? 'Simpan Perubahan' : 'Tambah Program'?></button>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Daftar Program (<?=count($programs)?>)</h3>
          <input type="search" id="program-search" class="form-control" style="width:min(240px,45%);" placeholder="Cari program..." aria-label="Cari program"/>
        </div>
        <div class="table-wrap">
          <table id="program-table">
            <thead>
              <tr><th>No</th><th>Nama Program</th><th>Deskripsi</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php if (!$programs): ?>
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">Belum ada data program.</td></tr>
              <?php else: ?>
                <?php foreach ($programs as $index => $program): ?>
                  <tr>
                    <td><?=$index + 1?></td>
                    <td><strong><?=clean($program->nama)?></strong></td>
                    <td><?=nl2br(clean($program->deskripsi ?? '—'))?></td>
                    <td>
                      <div style="display:flex;gap:8px;">
                        <a class="btn btn-sm btn-ghost" href="?action=edit&amp;id=<?=$program->id?>" aria-label="Edit <?=clean($program->nama)?>"><i class="fas fa-pen"></i> Edit</a>
                        <form method="POST" action="programs.php" onsubmit="return confirm('Hapus program ini?');">
                          <input type="hidden" name="_csrf" value="<?=clean($csrfToken)?>"/>
                          <input type="hidden" name="action" value="delete"/>
                          <input type="hidden" name="id" value="<?=$program->id?>"/>
                          <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
initTableSearch('program-search', 'program-table');
</script>
</body>
</html>
