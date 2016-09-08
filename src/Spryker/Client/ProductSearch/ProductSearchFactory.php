<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductSearch;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Shared\ProductSearch\Code\KeyBuilder\ProductSearchConfigExtensionKeyBuilder;

class ProductSearchFactory extends AbstractFactory
{

    /**
     * @return \Spryker\Shared\Collector\Code\KeyBuilder\KeyBuilderInterface
     */
    public function createProductSearchConfigExtensionKeyBuilder()
    {
        return new ProductSearchConfigExtensionKeyBuilder();
    }

    /**
     * @return \Spryker\Shared\Kernel\Store
     */
    public function getStore()
    {
        return $this->getProvidedDependency(ProductSearchDependencyProvider::STORE);
    }

}
