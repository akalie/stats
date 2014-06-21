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


</div>
</body>
</html>