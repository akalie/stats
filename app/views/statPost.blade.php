<?php
/**
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

    <form class="form" method="POST" action="" enctype="multipart/form-data"  width="300px">
        <h3 class="form-signin-heading">Вставьте id постов, которые необходимо распарсить на лайки/репосты. Это пока голый интерфейс, ничего не произойдет</h3>
        <br>
        <label for="id">Id постов(типа "-XXX_YYY"), через запятую, не больше 10 </label><br>
        <input type="text" value="" name="postIds" data-role="tagsinput" placeholder="Add tags" />
        <br>
        <label for="id">txt с Id постов(типа "-XXX_YYY"), через запятую</label><br>
        <input type="file" name="f" accept="text/txt"/>
        <br>
        <label for="label">Лейбл(по умолчанию будет текущее время)</label>
        <input type="text" name="label" class="form-control input-sm">
        <br>
        <br>
        <input class="btn btn-large btn-primary" type="submit" value="Добавить посты для парса" >
    </form>

    <br>
    <br>
    <?php if ( !is_null($queuesInfo)) {  ?>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Задача</th>
                <th>Лайки постов</th>
                <th>Репосты постов</th>
                <th>Все</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php  foreach( $queuesInfo as $queue) { ?>
                <tr>
                    <td><?= $queue['label'] ?></td>
                    <td><?= $queue['postLikes'] ? link_to('download/' . $queue['postLikes'], 'скачать') : 'В процессе' ?></td>
                    <td><?= $queue['postReposts'] ? link_to('download/' . $queue['postReposts'], 'скачать') : 'В процессе' ?></td>
                    <td><?= /*todo $queue['all'] ? link_to('download/' . $queue['all'], 'скачать') :*/ 'В процессе' ?></td>
                    <td><?= link_to('deleteQueueEx/' . $queue['queueId'], 'удалить') ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>

</div>
</body>
</html>
