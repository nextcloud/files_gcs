<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\FilesGCS\AppInfo\Application::APP_ID, OCA\FilesGCS\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\FilesGCS\AppInfo\Application::APP_ID, OCA\FilesGCS\AppInfo\Application::APP_ID . '-main');

?>

<div id="files-gcs"></div>
