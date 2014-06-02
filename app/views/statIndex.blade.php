<?php
/**
 * $from string
 * $to string
 * $idString string
 * $queuesInfo array
 */


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign in &middot; Twitter Bootstrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    {{ HTML::style('assets/css/bootstrap.css') }}

    <!--[if lt IE 9]>
    <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

</head>

<body>
<div class="container">
    <?php
        if ( $errorMsg ) {?>
            <h3 style="color: red;" class="form-signin-heading"><?=$errorMsg?></h3>
        <?php }
    ?>
    <form class="form" method="POST" action="" width="300px">
        <h3 class="form-signin-heading">Что считать будем?</h3>


        <br>
        <label for="name">Ссылка на источник</label>
        <input type="text" name="idString" class="form-control"  value="{{ $idString }}">
        <br>
        <br>
        <input class="btn btn-large btn-primary" type="submit" value="Добавить паблик" >
    </form>

    <?php if (!is_null($resultIds)) { ?>
        <table>
            <thead>
                <tr><th>Юзеры</th></tr>
            </thead>
            <tbody>
                <?php foreach( $resultIds as $id) { ?>
                    <tr><td><?= $id ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <?php if ( !is_null($queuesInfo)) {  ?>
    <table сlass="table">
        <thead>
        <tr><td>Паблик</td><td>Лайки постов</td>Репосты постов</td><td>Обсуждения</td><td>Лайки альбомов</td><td>репосты альбомов</td></tr>
        </thead>
        <tbody>
        <?php  foreach( $queuesInfo as $public) { ?>
            <tr>
                <td><?= $public['title'] ?></td>
                <td><?= $public['postLikes'] ? link_to('download/' . $public['postLikes'], 'скачать') : 'В процессе' ?></td>
                <td><?= $public['postReposts'] ? link_to('download/' . $public['postReposts'], 'скачать') : 'В процессе' ?></td>
                <td><?= $public['boardRepls'] ? link_to('download/' . $public['boardRepls'], 'скачать') : 'В процессе' ?></td>
                <td><?= $public['albumLikes'] ? link_to('download/' . $public['albumLikes'], 'скачать') : 'В процессе' ?></td>
                <td><?= $public['albumReposts'] ? link_to('download/' . $public['albumReposts'], 'скачать') : 'В процессе' ?></td>
            </tr>
    <?php } ?>
        </tbody>
    </table>
<?php } ?>
</div>


</body>
</html>
