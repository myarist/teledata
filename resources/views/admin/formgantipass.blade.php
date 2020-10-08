<div class="form-group">
    <label for="admin_password">Password Baru</label>
    <div class="controls">
    <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Password Baru" required data-validation-required-message="Harus terisi">
    </div>
</div>
<div class="form-group">
    <label for="admin_password_ulangi">Ulangi Password Baru</label>
    <div class="controls">
    <input type="password" class="form-control" id="admin_password_ulangi" name="admin_password_ulangi" placeholder="Ulangi Password Baru" required data-validation-match-match="admin_password" data-validation-match-message="Harus sama dengan Password" data-validation-required-message="Harus terisi">
    </div>
</div>
<input type="hidden" name="id" id="id" />