<?php

declare(strict_types=1);

use App\Modules\Content\Routes\ArticleRoute;

it(runsTestsAutomatically('article'), function () {
    ArticleRoute::tests($this)->run();
});
