/** global: Craft */
/** global: Garnish */

/**
 * ProductBundlesIndex class
 */

(function($) {
    if (typeof Craft.Bundles === 'undefined') {
        Craft.Bundles = {};
    }

    Craft.Bundles.ProductBundlesIndex = Craft.BaseElementIndex.extend({
        init: function (elementType, $container, settings) {
            this.on('selectSource', $.proxy(this, 'updateButton'));
            this.on('selectSite', $.proxy(this, 'updateButton'));
            this.base(elementType, $container, settings);
        },

        updateButton: function () {
            var href, label;

            label = Craft.t('commerce-product-bundles', 'Add product bundle');

            var uri = 'commerce/product-bundles/new';
            if (this.siteId && this.siteId !== Craft.primarySiteId) {
                for (var i = 0; i < Craft.sites.length; i++) {
                    if (Craft.sites[i].id === this.siteId) {
                        uri += '/' + Craft.sites[i].handle;
                    }
                }
            }
            href = 'href="' + Craft.getUrl(uri) + '"';

            this.addButton($('<a class="btn submit add icon" ' + href + '>' + Craft.escapeHtml(label) + '</a>'));
        }
    });

    // Register it!
    Craft.registerElementIndexClass('tde\\craft\\commerce\\bundles\\elements\\ProductBundle', Craft.Bundles.ProductBundlesIndex);

})(jQuery);
