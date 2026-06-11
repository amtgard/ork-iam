<?php

namespace Amtgard\IAM\Catalog;

/**
 * Catalog of built-in ORN prefix and segment label names.
 *
 * @see docs/ORN-ONTOLOGY.md
 */
enum ServiceCatalog: string
{
    case ORK = 'ORK';
    case Configuration = 'Configuration';
    case Mundane = 'Mundane';
    case Attendance = 'Attendance';
    case Kingdom = 'Kingdom';
    case Park = 'Park';
    case Unit = 'Unit';
    case Game = 'Game';
    case Event = 'Event';
    case EventInstance = 'EventInstance';
    case Awards = 'Awards';
    case Audit = 'Audit';
    case Cache = 'Cache';
    case Tenant = 'Tenant';
    case Officer = 'Officer';
    case Recommendations = 'Recommendations';
    case Tournament = 'Tournament';
    case Idp = 'Idp';
    case Documents = 'Documents';
    case Forums = 'Forums';
    case Media = 'Media';
    case Errata = 'Errata';
    case Application = 'Application';
}
