/** global: Craft */
/** global: Garnish */

/**
 * ProductBundlesIndex class
 */

if (typeof Craft.Bundles === 'undefined') {
    Craft.Bundles = {};
}

Craft.Bundles.ProductBundlesIndex = Craft.BaseElementIndex.extend({
    $newBtn: null,

    init: function (elementType, $container, settings) {
        this.on('selectSource', $.proxy(this, 'updateButton'));
        this.on('selectSite', $.proxy(this, 'updateButton'));
        this.base(elementType, $container, settings);
    },

    afterInit: function () {
        this.base();
    },

    updateButton: function () {
        if (!this.$source) {
            return;
        }

        var href, label, btn;

        // Remove the old button, if there is one
        if (this.$newBtn) {
            this.$newBtn.remove();
        }

        this.$newBtn = $('<div class="btngroup submit"/>');

        // determine href
        var uri = 'commerce/product-bundles/new';
        if (this.siteId && this.siteId !== Craft.primarySiteId) {
            for (var i = 0; i < Craft.sites.length; i++) {
                if (Craft.sites[i].id === this.siteId) {
                    uri += '/' + Craft.sites[i].handle;
                }
            }
        }
        href = 'href="' + Craft.getUrl(uri) + '"';

        // build & add button
        label = Craft.t('commerce-product-bundles', 'Add product bundle');
        btn = $('<a class="btn submit add icon" ' + href + '>' + Craft.escapeHtml(label) + '</a>').appendTo(this.$newBtn);

        this.addButton(this.$newBtn);
    }
});

// Register it!
Craft.registerElementIndexClass('oym\\craft\\commerce\\bundles\\elements\\ProductBundle', Craft.Bundles.ProductBundlesIndex);
