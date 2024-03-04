<?php

namespace tde\craft\commerce\bundles {
    spl_autoload_register(
        static function (string $className) {
            $old = 'tde\\craft\\commerce\\bundles\\';
            $new = 'oym\\craft\\commerce\\bundles\\';

            if (0 !== strpos($className, $old)) {
                return;
            }

            $newName = substr_replace($className, $new, 0, strlen($old));
            class_alias($newName, $className);
        },
        true,
        false,
    );

    if (!\class_exists(Plugin::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\Plugin */
        class Plugin
        {
        }
    }
}

namespace tde\craft\commerce\bundles\assetbundles\productbundles {
    if (!\class_exists(ProductBundlesAsset::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\assetbundles\productbundles\ProductBundlesAsset */
        class ProductBundlesAsset
        {
        }
    }
}

namespace tde\craft\commerce\bundles\assetbundles\productmatrix {
    if (!\class_exists(ProductMatrixAsset::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\assetbundles\productmatrix\ProductMatrixAsset */
        class ProductMatrixAsset
        {
        }
    }
}

namespace tde\craft\commerce\bundles\behaviors {
    if (!\class_exists(ProductBundleBehavior::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\behaviors\ProductBundleBehavior */
        class ProductBundleBehavior
        {
        }
    }
}

namespace tde\craft\commerce\bundles\controllers {
    if (!\class_exists(ProductBundlesController::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\controllers\ProductBundlesController */
        class ProductBundlesController
        {
        }
    }

    if (!\class_exists(ProductBundlesPreviewController::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\controllers\ProductBundlesPreviewController */
        class ProductBundlesPreviewController
        {
        }
    }

    if (!\class_exists(SettingsController::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\controllers\SettingsController */
        class SettingsController
        {
        }
    }
}

namespace tde\craft\commerce\bundles\elements {
    if (!\class_exists(ProductBundle::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\elements\ProductBundle */
        class ProductBundle
        {
        }
    }
}

namespace tde\craft\commerce\bundles\elements\db {
    if (!\class_exists(ProductBundleQuery::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\elements\db\ProductBundleQuery */
        class ProductBundleQuery
        {
        }
    }
}

namespace tde\craft\commerce\bundles\fieldlayoutelements {
    if (!\class_exists(ProductField::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\fieldlayoutelements\ProductField */
        class ProductField
        {
        }
    }
}

namespace tde\craft\commerce\bundles\fields {
    if (!\class_exists(ProductBundleField::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\fields\ProductBundleField */
        class ProductBundleField
        {
        }
    }
}

namespace tde\craft\commerce\bundles\helpers {
    if (!\class_exists(ProductBundleHelper::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\helpers\ProductBundleHelper */
        class ProductBundleHelper
        {
        }
    }
}

namespace tde\craft\commerce\bundles\helpers {
    if (!\class_exists(ProductMatrix::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\helpers\ProductMatrix */
        class ProductMatrix
        {
        }
    }
}

namespace tde\craft\commerce\bundles\migrations {
    if (!\class_exists(Install::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\migrations\Install */
        class Install
        {
        }
    }

    if (!\class_exists(m201029_160701_product_field_qty::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\migrations\m201029_160701_product_field_qty */
        class m201029_160701_product_field_qty
        {
        }
    }

    if (!\class_exists(m201029_165753_remove_shipping_tax_categories::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\migrations\m201029_165753_remove_shipping_tax_categories */
        class m201029_165753_remove_shipping_tax_categories
        {
        }
    }
}

namespace tde\craft\commerce\bundles\models {
    if (!\class_exists(ProductBundle::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\models\ProductBundle */
        class ProductBundle
        {
        }
    }

    if (!\class_exists(ProductBundleProduct::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\models\ProductBundleProduct */
        class ProductBundleProduct
        {
        }
    }

    if (!\class_exists(Settings::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\models\Settings */
        class Settings
        {
        }
    }
}

namespace tde\craft\commerce\bundles\records {
    if (!\class_exists(ProductBundle::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\records\ProductBundle */
        class ProductBundle
        {
        }
    }

    if (!\class_exists(ProductBundleProduct::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\records\ProductBundleProduct */
        class ProductBundleProduct
        {
        }
    }
}

namespace tde\craft\commerce\bundles\services {
    if (!\class_exists(ProductBundleService::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\services\ProductBundleService */
        class ProductBundleService
        {
        }
    }
}

namespace tde\craft\commerce\bundles\variables {
    if (!\class_exists(ProductBundlesVariable::class)) {
        /** @deprecated this is an alias for \oym\craft\commerce\bundles\variables\ProductBundlesVariable */
        class ProductBundlesVariable
        {
        }
    }
}
