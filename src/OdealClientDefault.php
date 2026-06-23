<?php

declare(strict_types=1);

namespace Odeal\Sdk;

use Attribute;

/**
 * Bu özellik ile işaretlenen alanlar OdealConfig üzerinden otomatik doldurulabilir.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OdealClientDefault
{
}