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
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Паблик</th>
                <th>Лайки постов</th>
                <th>Репосты постов</th>
                <th>Обсуждения</th>
                <th>Лайки альбомов</th>
                <th>Репосты альбомов</th>
            </tr>
            </thead>
            <tbody>
            <?php  foreach( $queuesInfo as $public) { ?>
                <tr>
                    <td><a href="http://vk.com/club<?= $public['publicId'] ?>"><?= $public['title'] ?></a></td>
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
