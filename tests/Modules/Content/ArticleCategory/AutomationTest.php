<?php

declare(strict_types=1);

use App\Modules\Content\Routes\ArticleCategoryRoute;

it(runsTestsAutomatically('category'), function () {
    ArticleCategoryRoute::tests($this)->run();
});
