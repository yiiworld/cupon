<?php if (Yii::app()->user->isGuest): ?>
<div id="uLogin-label">Войти через соцсети:</div>
<div id="uLogin" x-ulogin-params="display=<?php echo $display ?>;fields=<?php echo $fields ?>;providers=<?php echo $providers ?>;hidden=<?php echo $hidden ?>;redirect_uri=<?php echo urlencode($redirect) ?>"></div>

<?php else: ?>
    <?php
    //$anchor = 'Выйти ('.Yii::app()->user->getName().')';
    //echo CHtml::link($anchor, array($logout_url));
    ?>
<?php endif ?>