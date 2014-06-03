<?php
/**
 * $from string
 * $to string
 * $idString string
 * $queuesInfo array
 */


?>
@include('header')

<body>
@include('menu')
<div class="container">
    <?php
        if ( $errorMsg ) {?>
            <h3 style="color: red;" class="form-signin-heading"><?=$errorMsg?></h3>
        <?php }
    ?>
    <form class="form" method="POST" action="" width="300px">
        <h3 class="form-signin-heading">Вставьте токен</h3>
        <br>
        <label for="id">Id юзера</label>
        <input type="text" name="userId" class="form-control input-sm">
        <br>
        <label for="id">Токен</label>
        <input type="text" name="newToken" class="form-control input-sm">
        <br>
        <br>
        <input class="btn btn-large btn-primary" type="submit" value="Добавить токены" >
    </form>

    <?php if ( !empty($tokens)) {  ?>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Юзер</th>
                <th>токен</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php  foreach( $tokens as $token) { ?>
                <tr>
                    <td><a href="http://vk.com/id<?= $token->user_id ?>"><?= $token->user_id ?></a></td>
                    <td><?= $token->token ?></td>
                    <td><?= link_to('deleteToken/' . $token->id, 'удалить') ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>
</body>
</html>
