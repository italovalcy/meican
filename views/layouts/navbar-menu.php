<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>

<ul class="nav navbar-nav">
  <!-- Messages: style can be found in dropdown.less-->
  <?php if (!\Yii::$app->user->isGuest): ?>
  <li class="dropdown messages-menu">
    <!-- Menu toggle button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
      <i class="fa fa-envelope-o"></i>
      <span class="label label-success">4</span>
    </a>
    <ul class="dropdown-menu">
      <li class="header">You have 4 messages</li>
      <li>
        <!-- inner menu: contains the messages -->
        <ul class="menu">
          <li><!-- start message -->
            <a href="#">
              <div class="pull-left">
                <!-- User Image -->
                <?= Html::img("@web/images/avatar.png", ['class'=> 'img-circle']); ?>
              </div>
              <!-- Message title and timestamp -->
              <h4>
                Support
                <small><i class="fa fa-clock-o"></i> 5 mins</small>
              </h4>
              <!-- The message -->
              <p>Welcome to MEICAN.</p>
            </a>
          </li>
          <!-- end message -->
        </ul>
        <!-- /.menu -->
      </li>
      <li class="footer"><a href="#">See All Messages</a></li>
    </ul>
  </li>
  <!-- /.messages-menu -->

  <!-- Notifications Menu -->
  <li class="dropdown notifications-menu">
    <!-- Menu toggle button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
      <i class="fa fa-bell-o"></i>
      <span class="label label-warning">10</span>
    </a>
    <ul class="dropdown-menu">
      <li class="header">You have 10 notifications</li>
      <li>
        <!-- Inner Menu: contains the notifications -->
        <ul class="menu">
          <li><!-- start notification -->
            <a href="#">
              <i class="fa fa-users text-aqua"></i> 5 new members joined today
            </a>
          </li>
          <!-- end notification -->
        </ul>
      </li>
      <li class="footer"><a href="#">View all</a></li>
    </ul>
  </li>
  <?php endif; ?>
  <li><?= Html::a(Yii::t("init", "About"),array('/init/support/about')); ?></li>
  <li><?= Html::a(Yii::t("init", "Help"),array('/init/support/help')); ?></li>

  <?php if (!\Yii::$app->user->isGuest): ?>
  <!-- User Account Menu -->
  <li class="dropdown user user-menu">
    <!-- Menu Toggle Button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
      <!-- The user image in the navbar-->
      <?= Html::img("@web/images/avatar.png", ['class'=> 'user-image']); ?>
      <!-- hidden-xs hides the username on small devices so only the image appears. -->
      <span class="hidden-xs"><?= \Yii::$app->user->getIdentity()->name; ?></span>
    </a>
    <ul class="dropdown-menu">
      <!-- The user image in the menu -->
      <li class="user-header">
        <?= Html::img("@web/images/avatar.png", ['class'=> 'img-circle']); ?>

        <p>
          <?= \Yii::$app->user->getIdentity()->name; ?>
          <small><?= \Yii::$app->user->getIdentity()->email; ?></small>
        </p>
      </li>
      
      <!-- Menu Footer-->
      <li class="user-footer">
        <div class="pull-left">
          <a href="<?= Url::toRoute(['/aaa/user/account']); ?>" class="btn btn-default btn-flat">My account</a>
        </div>
        <div class="pull-right">
          <a href="#" class="btn btn-default btn-flat">Sign out</a>
        </div>
      </li>
    </ul>
  </li>
  <li><?= Html::a(Yii::t("init", "Sign out"),array('/init/login/logout')); ?></li>
  <?php else: ?>
  <li><?= Html::a(Yii::t("init", "Sign in"),array('/init/login')); ?></li>
  <?php endif; ?>
</ul>