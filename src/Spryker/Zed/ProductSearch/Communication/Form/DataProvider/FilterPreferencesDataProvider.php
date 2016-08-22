<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductSearch\Communication\Form\DataProvider;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Shared\ProductSearch\ProductSearchConstants;
use Spryker\Zed\ProductSearch\Communication\Form\AttributeTranslationForm;
use Spryker\Zed\ProductSearch\Communication\Form\FilterPreferencesForm;
use Spryker\Zed\ProductSearch\Dependency\Facade\ProductSearchToGlossaryInterface;
use Spryker\Zed\ProductSearch\Dependency\Facade\ProductSearchToLocaleInterface;
use Spryker\Zed\ProductSearch\Persistence\ProductSearchQueryContainerInterface;
use Spryker\Zed\ProductSearch\ProductSearchConfig;

class FilterPreferencesDataProvider
{

    /**
     * @var \Spryker\Zed\ProductSearch\Persistence\ProductSearchQueryContainerInterface
     */
    protected $productSearchQueryContainer;

    /**
     * @var \Spryker\Zed\ProductSearch\ProductSearchConfig
     */
    protected $config;

    /**
     * @var \Spryker\Zed\ProductSearch\Dependency\Facade\ProductSearchToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\ProductSearch\Dependency\Facade\ProductSearchToGlossaryInterface
     */
    protected $glossaryFacade;

    /**
     * @param \Spryker\Zed\ProductSearch\Persistence\ProductSearchQueryContainerInterface $productSearchQueryContainer
     * @param \Spryker\Zed\ProductSearch\ProductSearchConfig $config
     * @param \Spryker\Zed\ProductSearch\Dependency\Facade\ProductSearchToLocaleInterface $localeFacade
     * @param \Spryker\Zed\ProductSearch\Dependency\Facade\ProductSearchToGlossaryInterface $glossaryFacade
     */
    public function __construct(
        ProductSearchQueryContainerInterface $productSearchQueryContainer,
        ProductSearchConfig $config,
        ProductSearchToLocaleInterface $localeFacade,
        ProductSearchToGlossaryInterface $glossaryFacade
    ) {
        $this->productSearchQueryContainer = $productSearchQueryContainer;
        $this->config = $config;
        $this->localeFacade = $localeFacade;
        $this->glossaryFacade = $glossaryFacade;
    }

    /**
     * @param int $idProductSearchAttribute
     *
     * @return array
     */
    public function getData($idProductSearchAttribute = null)
    {
        if (!$idProductSearchAttribute) {
            return [
                FilterPreferencesForm::FIELD_TRANSLATIONS => $this->getTranslationFields(),
            ];
        }

        $productSearchAttributeEntity = $this->getProductSearchAttributeEntity($idProductSearchAttribute);

        $attributeKey = $productSearchAttributeEntity->getSpyProductAttributeKey()->getKey();

        $data = [
            FilterPreferencesForm::FIELD_ID_PRODUCT_SEARCH_ATTRIBUTE => $productSearchAttributeEntity->getIdProductSearchAttribute(),
            FilterPreferencesForm::FIELD_KEY => $attributeKey,
            FilterPreferencesForm::FIELD_FILTER_TYPE => $productSearchAttributeEntity->getFilterType(),
            FilterPreferencesForm::FIELD_TRANSLATIONS => $this->getTranslationFields($attributeKey),
        ];

        return $data;
    }

    /**
     * @param int $idProductSearchAttribute
     *
     * @return array
     */
    public function getOptions($idProductSearchAttribute = null)
    {
        $options = [
            FilterPreferencesForm::OPTION_FILTER_TYPE_CHOICES => $this->config->getFilterTypeChoices(),
            FilterPreferencesForm::OPTION_IS_UPDATE => ($idProductSearchAttribute > 0),
        ];

        return $options;
    }

    /**
     * @param int $idProductSearchAttribute
     *
     * @return \Orm\Zed\ProductSearch\Persistence\SpyProductSearchAttribute|null
     */
    protected function getProductSearchAttributeEntity($idProductSearchAttribute)
    {
        $attributeEntity = $this->productSearchQueryContainer
            ->queryFilterPreferencesTable()
            ->findOneByIdProductSearchAttribute($idProductSearchAttribute);

        return $attributeEntity;
    }

    /**
     * @param string|null $attributeKey
     *
     * @return array
     */
    protected function getTranslationFields($attributeKey = null)
    {
        $availableLocales = $this->localeFacade->getLocaleCollection();

        $fields = [];
        foreach ($availableLocales as $localeTransfer) {
            $localizedData = [
                AttributeTranslationForm::FIELD_KEY_TRANSLATION => null,
            ];

            if ($attributeKey) {
                $localizedData = [
                    AttributeTranslationForm::FIELD_KEY_TRANSLATION => $this->getAttributeKeyTranslation($attributeKey, $localeTransfer),
                ];
            }

            $fields[$localeTransfer->getLocaleName()] = $localizedData;
        }

        return $fields;
    }

    /**
     * @param string $attributeKey
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    protected function getAttributeKeyTranslation($attributeKey, LocaleTransfer $localeTransfer)
    {
        $glossaryKey = ProductSearchConstants::PRODUCT_SEARCH_FILTER_GLOSSARY_PREFIX . $attributeKey;

        if ($this->glossaryFacade->hasTranslation($glossaryKey, $localeTransfer)) {
            return $this->glossaryFacade
                ->getTranslation($glossaryKey, $localeTransfer)
                ->getValue();
        }

        return null;
    }

}
