<div class="profile__grid" id="tab-info">
    <div class="settings-content" style="width:100%;">
        <div class="profile-info-section">
            <div class="profile-header">
                <h2>Профиль</h2>
                <button class="edit-btn" onclick="document.getElementById('editModal').style.display='flex'">
                    РЕДАКТИРОВАТЬ <img src="public/edit_profile.png" alt="">
                </button>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Имя</span>
                    <span class="info-value"><?=$USER['name']?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?=$USER['email']?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Телефон</span>
                    <span class="info-value"><?=$USER['phone'] ?? 'Не указан'?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Дата регистрации</span>
                    <span class="info-value">
                        <?php
                            if(!empty($USER['created_at'])){
                                echo date('d.m.Y', strtotime($USER['created_at']));
                            }else{
                                echo '—';
                            }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
