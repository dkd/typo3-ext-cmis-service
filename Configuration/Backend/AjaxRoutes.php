<?php
/**
 * Definitions for routes provided by EXT:cmis_service
 */
return [
    'cmis_updateConnections' => [
        'path' => '/cmis/updateConnections',
        'target' => \Dkd\CmisService\ConnectionManager::class . '::updateConnectionsInCacheMenu'
    ]
];