(function($) {
  if (typeof Craft.Bundles === 'undefined') {
    Craft.Bundles = {};
  }

    Craft.Bundles.ProductMatrix = Garnish.Base.extend(
        {
            id: null,
            fieldBodyHtml: null,
            fieldFootHtml: null,
            inputNamePrefix: null,
            inputIdPrefix: null,

            $container: null,
            $productContainer: null,
            $addProductBtn: null,

            productSort: null,
            productSelect: null,
            defaultProduct: null,
            totalNewProducts: 0,
            singleColumnMode: false,

            init: function(id, fieldBodyHtml, fieldFootHtml, inputNamePrefix) {
                this.id = id;
                this.fieldBodyHtml = fieldBodyHtml;
                this.fieldFootHtml = fieldFootHtml;
                this.inputNamePrefix = inputNamePrefix;
                this.inputIdPrefix = Craft.formatInputId(this.inputNamePrefix);

                this.$container = $('#' + this.id);
                this.$productContainer = this.$container.children('.blocks');
                this.$addProductBtn = this.$container.children('.btn');

                var $products = this.$productContainer.children(),
                    collapsedProducts = Craft.Bundles.ProductMatrix.getCollapsedProductIds();

                this.productSort = new Garnish.DragSort($products, {
                    handle: '> .actions > .move',
                    axis: 'y',
                    filter: $.proxy(function() {
                        // Only return all the selected items if the target item is selected
                        if (this.productSort.$targetItem.hasClass('sel')) {
                            return this.productSelect.getSelectedItems();
                        }
                        else {
                            return this.productSort.$targetItem;
                        }
                    }, this),
                    collapseDraggees: true,
                    magnetStrength: 4,
                    helperLagBase: 1.5,
                    helperOpacity: 0.9,
                    onSortChange: $.proxy(function() {
                        this.productSelect.resetItemOrder();
                    }, this)
                });

                this.productSelect = new Garnish.Select(this.$productContainer, $products, {
                    multi: true,
                    vertical: true,
                    handle: '> .checkbox, > .titlebar',
                    checkboxMode: true
                });

                for (var i = 0; i < $products.length; i++) {
                    var $product = $products.eq(i),
                        id = $product.data('id');

                    // Is this a new product?
                    var newMatch = (typeof id === 'string' && id.match(/new(\d+)/));

                    if (newMatch && newMatch[1] > this.totalNewProducts) {
                        this.totalNewProducts = parseInt(newMatch[1]);
                    }

                    var product = new Product(this, $product);

                    if (product.id && $.inArray('' + product.id, collapsedProducts) !== -1) {
                        product.collapse();
                    }

                    // Init the unlimited stock checkbox
                    Craft.Commerce.initUnlimitedStockCheckbox($product);
                }

                this.addListener(this.$addProductBtn, 'click', function() {
                    this.addProduct();
                });

                this.addListener(this.$container, 'resize', 'handleContainerResize');
                Garnish.$doc.ready($.proxy(this, 'handleContainerResize'));

                if (this.$container.width()) {
                    this.handleContainerResize();
                }
            },

            setDefaultProduct: function(product) {
                if (this.defaultProduct) {
                    this.defaultProduct.unsetAsDefault();
                }

                product.setAsDefault();
                this.defaultProduct = product;
            },

            addProduct: function($insertBefore) {
                this.totalNewProducts++;

                var id = 'new' + this.totalNewProducts;

                var $product = $(
                    '<div class="product-matrixblock matrixblock" data-id="' + id + '">' +
                    '<div class="titlebar">' +
                    '<div class="preview"></div>' +
                    '</div>' +
                    '<div class="checkbox" title="' + Craft.t('commerce-product-bundles', 'Select') + '"></div>' +
                    '<div class="actions">' +
                    '<a class="settings icon menubtn" title="' + Craft.t('commerce-product-bundles', 'Actions') + '" role="button"></a> ' +
                    '<div class="menu">' +
                    '<ul class="padded">' +
                    '<li><a data-icon="collapse" data-action="collapse">' + Craft.t('commerce-product-bundles', 'Collapse') + '</a></li>' +
                    '<li class="hidden"><a data-icon="expand" data-action="expand">' + Craft.t('commerce-product-bundles', 'Expand') + '</a></li>' +
                    '</ul>' +
                    '<hr class="padded"/>' +
                    '<ul class="padded">' +
                    '<li><a data-icon="+" data-action="add">' + Craft.t('commerce-product-bundles', 'Add product above') + '</a></li>' +
                    '</ul>' +
                    '<hr class="padded"/>' +
                    '<ul class="padded">' +
                    '<li><a data-icon="remove" data-action="delete">' + Craft.t('commerce-product-bundles', 'Delete') + '</a></li>' +
                    '</ul>' +
                    '</div>' +
                    '<a class="move icon" title="' + Craft.t('commerce-product-bundles', 'Reorder') + '" role="button"></a> ' +
                    '</div>' +
                    '</div>'
                );

                if ($insertBefore) {
                    $product.insertBefore($insertBefore);
                }
                else {
                    $product.appendTo(this.$productContainer);
                }

                var $fieldsContainer = $('<div class="fields"/>').appendTo($product),
                    bodyHtml = this.getParsedProductHtml(this.fieldBodyHtml, id),
                    footHtml = this.getParsedProductHtml(this.fieldFootHtml, id);

                var $body = $(bodyHtml);
                $body.find('#related-sales-field').remove();

                $body.appendTo($fieldsContainer);

                if (this.singleColumnMode) {
                    this.setProductsToSingleColMode($product);
                }

                // Animate the product into position
                $product.css(this.getHiddenProductCss($product)).velocity({
                    opacity: 1,
                    'margin-bottom': 10
                }, 'fast', $.proxy(function() {
                    $product.css('margin-bottom', '');
                    Garnish.$bod.append(footHtml);
                    Craft.initUiElements($fieldsContainer);
                    Craft.Commerce.initUnlimitedStockCheckbox($product);
                    var product = new Product(this, $product);
                    this.productSort.addItems($product);
                    this.productSelect.addItems($product);

                    Garnish.requestAnimationFrame(function() {
                        // Scroll to the product
                        Garnish.scrollContainerToElement($product);
                    });

                    // If this is the only product, set it as the default
                    if (this.$productContainer.children().length === 1) {
                        this.setDefaultProduct(product);
                    }
                }, this));
            },

            collapseSelectedProducts: function() {
                this.callOnSelectedProducts('collapse');
            },

            expandSelectedProducts: function() {
                this.callOnSelectedProducts('expand');
            },

            disableSelectedProducts: function() {
                this.callOnSelectedProducts('disable');
            },

            enableSelectedProducts: function() {
                this.callOnSelectedProducts('enable');
            },

            deleteSelectedProducts: function() {
                this.callOnSelectedProducts('selfDestruct');
            },

            callOnSelectedProducts: function(fn) {
                for (var i = 0; i < this.productSelect.$selectedItems.length; i++) {
                    this.productSelect.$selectedItems.eq(i).data('product')[fn]();
                }
            },

            getHiddenProductCss: function($product) {
                return {
                    opacity: 0,
                    marginBottom: -($product.outerHeight())
                };
            },

            getParsedProductHtml: function(html, id) {
                if (typeof html === 'string') {
                    return html.replace(/__PRODUCT__/g, id);
                }
                else {
                    return '';
                }
            },

            handleContainerResize: function() {
                if (this.$container.width() < 700) {
                    if (!this.singleColumnMode) {
                        this.setProductsToSingleColMode(this.productSort.$items);
                        this.singleColumnMode = true;
                    }
                } else {
                    if (this.singleColumnMode) {
                        this.setProductsToTwoColMode(this.productSort.$items);
                        this.productSort.$items.removeClass('single-col');
                        this.singleColumnMode = false;
                    }
                }
            },

            setProductsToSingleColMode: function($products) {
                $products
                    .addClass('single-col')
                    .find('> .fields > .custom-fields').addClass('meta');
            },

            setProductsToTwoColMode: function($products) {
                $products
                    .removeClass('single-col')
                    .find('> .fields > .custom-fields').removeClass('meta');
            }
        },
        {
            collapsedProductStorageKey: 'Craft-' + Craft.siteUid + '.Commerce.ProductMatrix.collapsedProducts',

            getCollapsedProductIds: function() {
                if (typeof localStorage[Craft.Bundles.ProductMatrix.collapsedProductStorageKey] === 'string') {
                    return Craft.filterArray(localStorage[Craft.Bundles.ProductMatrix.collapsedProductStorageKey].split(','));
                }
                else {
                    return [];
                }
            },

            setCollapsedProductIds: function(ids) {
                localStorage[Craft.Commerce.ProductMatrix.collapsedProductStorageKey] = ids.join(',');
            },

            rememberCollapsedProductId: function(id) {
                if (typeof Storage !== 'undefined') {
                    var collapsedProducts = Craft.Commerce.ProductMatrix.getCollapsedProductIds();

                    if ($.inArray('' + id, collapsedProducts) === -1) {
                        collapsedProducts.push(id);
                        Craft.Commerce.ProductMatrix.setCollapsedProductIds(collapsedProducts);
                    }
                }
            },

            forgetCollapsedProductId: function(id) {
                if (typeof Storage !== 'undefined') {
                    var collapsedProducts = Craft.Commerce.ProductMatrix.getCollapsedProductIds(),
                        collapsedProductsIndex = $.inArray('' + id, collapsedProducts);

                    if (collapsedProductsIndex !== -1) {
                        collapsedProducts.splice(collapsedProductsIndex, 1);
                        Craft.Commerce.ProductMatrix.setCollapsedProductIds(collapsedProducts);
                    }
                }
            }
        });


    var Product = Garnish.Base.extend(
        {
            matrix: null,
            $container: null,
            $titlebar: null,
            $fieldsContainer: null,
            $previewContainer: null,
            $actionMenu: null,
            $collapsedInput: null,
            $defaultInput: null,
            $defaultBtn: null,

            isNew: null,
            id: null,

            collapsed: false,

            init: function(matrix, $container) {
                this.matrix = matrix;
                this.$container = $container;
                this.$titlebar = $container.children('.titlebar');
                this.$previewContainer = this.$titlebar.children('.preview');
                this.$fieldsContainer = $container.children('.fields');
                this.$defaultInput = this.$container.children('.default-input');
                this.$defaultBtn = this.$container.find('> .actions > .default-btn');

                this.$container.data('product', this);

                this.id = this.$container.data('id');
                this.isNew = (!this.id || (typeof this.id === 'string' && this.id.substr(0, 3) === 'new'));

                var $menuBtn = this.$container.find('> .actions > .settings'),
                    menuBtn = new Garnish.MenuBtn($menuBtn);

                this.$actionMenu = menuBtn.menu.$container;

                menuBtn.menu.settings.onOptionSelect = $.proxy(this, 'onMenuOptionSelect');

                // Was this product already collapsed?
                if (Garnish.hasAttr(this.$container, 'data-collapsed')) {
                    this.collapse();
                }

                this.addListener(this.$titlebar, 'dblclick', function(ev) {
                    ev.preventDefault();
                    this.toggle();
                });

                // Is this product the default?
                if (this.$defaultInput.val() === '1') {
                    this.matrix.setDefaultProduct(this);
                }

                this.addListener(this.$defaultBtn, 'click', function(ev) {
                    ev.preventDefault();
                    this.matrix.setDefaultProduct(this);
                });
            },

            toggle: function() {
                if (this.collapsed) {
                    this.expand();
                }
                else {
                    this.collapse(true);
                }
            },

            collapse: function(animate) {
                if (this.collapsed) {
                    return;
                }

                this.$container.addClass('collapsed');

                var previewHtml = '',
                    $fields = this.$fieldsContainer.find('> .meta > .field:first-child, > .custom-fields > .field');

                for (var i = 0; i < $fields.length; i++) {
                    var $field = $($fields[i]),
                        $inputs = $field.children('.input').find('select,input[type!="hidden"],textarea,.label'),
                        inputPreviewText = '';

                    for (var j = 0; j < $inputs.length; j++) {
                        var $input = $($inputs[j]),
                            value;

                        if ($input.hasClass('label')) {
                            var $maybeLightswitchContainer = $input.parent().parent();

                            if ($maybeLightswitchContainer.hasClass('lightswitch') && (
                                    ($maybeLightswitchContainer.hasClass('on') && $input.hasClass('off')) ||
                                    (!$maybeLightswitchContainer.hasClass('on') && $input.hasClass('on'))
                                )) {
                                continue;
                            }

                            value = $input.text();
                        }
                        else {
                            value = Craft.getText(Garnish.getInputPostVal($input));
                        }

                        if (value instanceof Array) {
                            value = value.join(', ');
                        }

                        if (value) {
                            value = Craft.trim(value);

                            if (value) {
                                if (inputPreviewText) {
                                    inputPreviewText += ', ';
                                }

                                inputPreviewText += value;
                            }
                        }
                    }

                    if (inputPreviewText) {
                        previewHtml += (previewHtml ? ' <span>|</span> ' : '') + inputPreviewText;
                    }
                }

                this.$previewContainer.html(previewHtml);

                this.$fieldsContainer.velocity('stop');
                this.$container.velocity('stop');

                if (animate) {
                    this.$fieldsContainer.velocity('fadeOut', {duration: 'fast'});
                    this.$container.velocity({height: 30}, 'fast');
                }
                else {
                    this.$previewContainer.show();
                    this.$fieldsContainer.hide();
                    this.$container.css({height: 30});
                }

                setTimeout($.proxy(function() {
                    this.$actionMenu.find('a[data-action=collapse]:first').parent().addClass('hidden');
                    this.$actionMenu.find('a[data-action=expand]:first').parent().removeClass('hidden');
                }, this), 200);

                // Remember that?
                if (!this.isNew) {
                    Craft.Commerce.ProductMatrix.rememberCollapsedProductId(this.id);
                }
                else {
                    if (!this.$collapsedInput) {
                        this.$collapsedInput = $('<input type="hidden" name="' + this.matrix.inputNamePrefix + '[' + this.id + '][collapsed]" value="1"/>').appendTo(this.$container);
                    }
                    else {
                        this.$collapsedInput.val('1');
                    }
                }

                this.collapsed = true;
            },

            expand: function() {
                if (!this.collapsed) {
                    return;
                }

                this.$container.removeClass('collapsed');

                this.$fieldsContainer.velocity('stop');
                this.$container.velocity('stop');

                var collapsedContainerHeight = this.$container.height();
                this.$container.height('auto');
                this.$fieldsContainer.css('display', 'flex');
                var expandedContainerHeight = this.$container.height();
                this.$container.height(collapsedContainerHeight);
                this.$fieldsContainer.hide().velocity('fadeIn', {duration: 'fast', display: 'flex'});
                this.$container.velocity({height: expandedContainerHeight}, 'fast', $.proxy(function() {
                    this.$previewContainer.html('');
                    this.$container.height('auto');
                }, this));

                setTimeout($.proxy(function() {
                    this.$actionMenu.find('a[data-action=collapse]:first').parent().removeClass('hidden');
                    this.$actionMenu.find('a[data-action=expand]:first').parent().addClass('hidden');
                }, this), 200);

                // Remember that?
                if (!this.isNew && typeof Storage !== 'undefined') {
                    var collapsedProducts = Craft.Commerce.ProductMatrix.getCollapsedProductIds(),
                        collapsedProductsIndex = $.inArray('' + this.id, collapsedProducts);

                    if (collapsedProductsIndex !== -1) {
                        collapsedProducts.splice(collapsedProductsIndex, 1);
                        Craft.Commerce.ProductMatrix.setCollapsedProductIds(collapsedProducts);
                    }
                }

                if (!this.isNew) {
                    Craft.Commerce.ProductMatrix.forgetCollapsedProductId(this.id);
                }
                else if (this.$collapsedInput) {
                    this.$collapsedInput.val('');
                }

                this.collapsed = false;
            },

            disable: function() {
                if (this.isDefault()) {
                    // Can't disable the default product
                    return false;
                }

                this.$container.children('input[name$="[enabled]"]:first').val('');
                this.$container.addClass('disabled');

                setTimeout($.proxy(function() {
                    this.$actionMenu.find('a[data-action=disable]:first').parent().addClass('hidden');
                    this.$actionMenu.find('a[data-action=enable]:first').parent().removeClass('hidden');
                }, this), 200);

                this.collapse(true);

                return true;
            },

            enable: function() {
                this.$container.children('input[name$="[enabled]"]:first').val('1');
                this.$container.removeClass('disabled');

                setTimeout($.proxy(function() {
                    this.$actionMenu.find('a[data-action=disable]:first').parent().removeClass('hidden');
                    this.$actionMenu.find('a[data-action=enable]:first').parent().addClass('hidden');
                }, this), 200);

                return true;
            },

            setAsDefault: function() {
                this.$defaultInput.val('1');
                this.$defaultBtn
                    .addClass('sel')
                    .attr('title', '');

                // Default products must be enabled
                this.enable();
                this.$actionMenu.find('a[data-action=disable]:first').parent().addClass('disabled');
            },

            unsetAsDefault: function() {
                this.$defaultInput.val('');
                this.$defaultBtn
                    .removeClass('sel')
                    .attr('title', 'Set as the default product');

                this.$actionMenu.find('a[data-action=disable]:first').parent().removeClass('disabled');
            },

            isDefault: function() {
                return this.$defaultInput.val() === '1';
            },

            onMenuOptionSelect: function(option) {
                var batchAction = (this.matrix.productSelect.totalSelected > 1 && this.matrix.productSelect.isSelected(this.$container)),
                    $option = $(option);

                switch ($option.data('action')) {
                    case 'collapse': {
                        if (batchAction) {
                            this.matrix.collapseSelectedProducts();
                        }
                        else {
                            this.collapse(true);
                        }

                        break;
                    }

                    case 'expand': {
                        if (batchAction) {
                            this.matrix.expandSelectedProducts();
                        }
                        else {
                            this.expand();
                        }

                        break;
                    }

                    case 'disable': {
                        if (batchAction) {
                            this.matrix.disableSelectedProducts();
                        }
                        else {
                            this.disable();
                        }

                        break;
                    }

                    case 'enable': {
                        if (batchAction) {
                            this.matrix.enableSelectedProducts();
                        }
                        else {
                            this.enable();
                            this.expand();
                        }

                        break;
                    }

                    case 'add': {
                        this.matrix.addProduct(this.$container);
                        break;
                    }

                    case 'delete': {
                        if (batchAction) {
                            if (confirm(Craft.t('commerce-product-bundles', 'Are you sure you want to delete the selected products?'))) {
                                this.matrix.deleteSelectedProducts();
                            }
                        }
                        else {
                            this.selfDestruct();
                        }

                        break;
                    }
                }
            },

            selfDestruct: function() {
                this.$container.velocity(this.matrix.getHiddenProductCss(this.$container), 'fast', $.proxy(function() {
                    this.$container.remove();

                    // If this is the default product, set the first product as default instead
                    if (this.isDefault()) {
                        var product = this.matrix.$productContainer.children(':first-child').data('product');

                        if (product) {
                            this.matrix.setDefaultProduct(product);
                        }
                    }
                }, this));
            }
        });


})(jQuery);
