<?php

declare(strict_types=1);

use App\Routes\System\RoleRoute;

it(runsTestsAutomatically('role'), function () {
    RoleRoute::tests($this)->run();
});
