<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Viewer\Listener;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Viewer\AppInfo\Application;
use OCA\Viewer\Event\LoadViewer;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IPreview;
use OCP\Util;

/**
 * @template-implements IEventListener<Event>
 * @psalm-api
 */
class LoadViewerScript implements IEventListener {
	private IInitialState $initialStateService;
	private IPreview $previewManager;
	private IAppConfig $appConfig;

	public function __construct(
		IInitialState $initialStateService,
		IPreview $previewManager,
		IAppConfig $appConfig,
	) {
		$this->initialStateService = $initialStateService;
		$this->previewManager = $previewManager;
		$this->appConfig = $appConfig;
	}

	public function handle(Event $event): void {
		if (!($event instanceof LoadViewer || $event instanceof LoadAdditionalScriptsEvent)) {
			return;
		}

		Util::addStyle(Application::APP_ID, 'viewer-init');

		$alwaysShowViewer = $this->appConfig->getAppValue('always_show_viewer', 'no') === 'yes';

		Util::addStyle(Application::APP_ID, 'viewer-main');
		Util::addInitScript(Application::APP_ID, 'viewer-init');
		Util::addScript(Application::APP_ID, 'viewer-main', 'files');
		$this->initialStateService->provideInitialState('enabled_preview_providers', array_keys($this->previewManager->getProviders()));
		$this->initialStateService->provideInitialState('always_show_viewer', $alwaysShowViewer);
	}
}
