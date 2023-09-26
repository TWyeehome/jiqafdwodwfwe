<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("./components/meta.php"); ?>
    <style>
        body,
        a {
            color: #0D0D10;
        }

        a:hover {
            color: #0D0D10;
        }
    </style>
</head>

<body>
    <main id="vueapp" class="pack pack--pc">
        <section class="subject subject--form align-items-center justify-content-center d-flex">
            <article class="w-100">
                <div class="">
                    <h3 class="subject__title mb-0" v-html="title">付款完成</h3>
                    <article id="result" class="my-3"></article>
                    <a class="h5 text-center" href="./event.php" v-if="buyStatus == 'a'">返回 WALLET</a>
                    <a class="h5 text-center" href="./index.php" v-else>返回購票頁</a>
                </div>
            </article>
        </section>
    </main>
    <?php require_once("./components/source.php"); ?>
</body>

</html>