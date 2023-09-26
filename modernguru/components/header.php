<?php
$header_title = "AI靈感大師:澳洲3D光影觸動樂園<br>MODERN GURU AND THE PATH TO ARTIFICIAL HAPPINESS";
$header_subtitle =
    '<div class="Mainheader__menubtn">
        <div class="Mainheader__menubtn__box">
            <nav class="Mainheader__menubtn__box__menu">
                <ul>
                    <li style="cursor: pointer;" @click="login();">LOGIN</li>
                </ul>
            </nav>
        </div>
    </div>';
?>
<header class="Mainheader">
    <div class="Mainheader__logo">
        <!--<div class="Mainheader__logo__txt d-block">
            <div class="d-flex">
                <div style="font-family: 'inter';">AI</div>
                <div style="font-family: '思源黑';">靈感大師:澳洲</div>
                <div style="font-family: 'inter';">3D</div>
                <div style="font-family: '思源黑';">光影觸動樂園</div>
            </div>
            <div style="font-family: Inter;">MODERN GURU AND THE PATH TO ARTIFICIAL HAPPINES</div>
        </div>-->
        <div class="Mainheader__logo__txt"><?php echo $header_title; ?></div>
    </div>
    <?php echo $header_subtitle ?>
</header>
