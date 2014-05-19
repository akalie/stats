<?php
/**
 * $from string
 * $to string
 * $idString string
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

    <!-- Le styles -->
    {{ HTML::style('assets/css/bootstrap.css') }}

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

</head>

<body>
<div class="container">
    <?php
        if ( $errorMsg ) {?>
            <h3 style="color: red;" class="form-signin-heading"><?=$errorMsg?></h3>
        <?}
    ?>
    <form class="form" method="POST" action="" width="300px">
        <h3 class="form-signin-heading">Что считать будем?</h3>

        <select class="form-control" width="200px" name="type" >
            <option value="repost">Репосты</option>
            <option value="likes">Лайки</option>
            <option value="borderComments">Комментаторы обсуждения</option>
        </select>
        <br>
        <label for="name">Ссылка на источник</label>
        <input type="text" name="idString" class="form-control"  value="{{ $idString }}">
        <br>
        <br>
        <input class="btn btn-large btn-primary" type="submit" >
    </form>

    <?php if ( !is_null($resultIds)) { ?>
        <table>
            <thead>
                <tr><td>Юзеры</td></td></tr>
            </thead>
            <tbody>
                <?php foreach( $resultIds as $id) { ?>
                    <tr><td><?= $id ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

</div> <!-- /container -->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->


</body>
</html>
