var ProductReviews = Class.create();
ProductReviews.prototype = {

    initialize: function(formId, listId, updateUrl, createUrl, reviewHtml, ajaxErrorMessage)
    {
        this.form = $(formId);
        this.list = $(listId);
        this.messageManager = new ProductReviews.MessageManager();
        this.updateUrl = updateUrl;
        this.createUrl = createUrl;
        this.template = new Template(reviewHtml);
        this.createLoader = null;
        this.updateLoader = null;
        this.thumbsLoader = null;
        this.ajaxErrorMessage = ajaxErrorMessage;
        this.loadState = false;
        this.captchaErrorMessage = '';
        this.updatePage = 1;
        /*
         IE does not understand event.stop in some Prototype versions
         so we have to move this function to button instead
         */
        //Event.observe(this.form,'submit',this.submit.bind(this),false);
        //this.validator = new Validation(this.form);

        // check the hash
        this.list.observe('list:afterupdate', this.listAfterUpdate.bind(this));
        this.fields = [];
    },

    submit: function()
    {
        if (this.validate()) {
            this.create();
        }
//        Event.stop(e);
        return false;
    },

    update: function()
    {
        if (this.loadState) {
            return;
        }
        if (this.updateLoader)
            this.updateLoader.show();
        this.loadState = true;
        new Ajax.Request(this.updateUrl + 'p/' + this.updatePage, {
            method:'get',
            onSuccess:this.onSuccessAjaxLoadUpdate.bind(this),
            onFailure:this.onFailureAjaxLoad.bind(this),
            onComplete:this.onCompleteAjaxLoad.bind(this)
        });
    },

    returnValidation: function (res)
    {
        this.loadState = false;
        if (this.createLoader)
            this.createLoader.hide();
        if (res) {
            this.create();
        } else {
            this.addError(this.captchaErrorMessage);
            this.captcha.reload();
        }
    },

    validate: function ()
    {
        var noError = true;
        this.fields.each(function(field) {
            if (!field.validate()) {
                noError = false;
            }
        });
        if (!noError) {
            return noError;
        }
        if (this.captcha) {
            if (this.loadState) {
                return false;
            }
            this.loadState = true;
            if (this.createLoader)
                this.createLoader.show();
            this.captcha.verify(this);
            /*
            captcha will return the Verify results to returnValidation function
             */
            return false;
        }
        return noError;
    },

    create: function()
    {
        if (this.loadState) {
            return;
        }
        if (this.createLoader)
            this.createLoader.show();
        this.loadState = true;
        new Ajax.Request(this.createUrl, {
            method:'post',
            parameters: this.form.serialize(),
            onSuccess:this.onSuccessAjaxLoadCreate.bind(this),
            onFailure:this.onFailureAjaxLoad.bind(this),
            onComplete:this.onCompleteAjaxLoad.bind(this)
        });
    },

    onSuccessAjaxLoadUpdate: function(transport)
    {
        var response;
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error) {
            this.addError(response.error_message);
        } else if (response.empty) {
            // do nothing for now
        } else {
            var returnHtml = '';
            var reviewsObj = this;
            response.items.each(function(item) {
                returnHtml += reviewsObj.template.evaluate(item);
            });
            this.list.insert(returnHtml);
            var button = $('update-reviews-button');
            if (response.last_page_number == this.updatePage) {
                button.hide();
            } else {
                button.show();
                this.updatePage += 1;
            }
            this.list.fire('list:afterupdate');
        }
    },

    onSuccessAjaxLoadCreate: function(transport)
    {
        var response;
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error) {
            this.addError(response.error_message);
        } else if (!response.can_show) {
            this.messageManager.show(response.success_message, 'success');
        } else {
            var returnHtml = this.template.evaluate(response.item);

            Element.insert(this.list, {top: returnHtml});
            this.messageManager.show(response.success_message, 'success');
        }

        this.fields.each(function(field) {
            field.field.value = field.emptyText;
            field.clear();
        });
        this.form.fire('form:aftersubmit');
        this.clearReply();
    },

    onFailureAjaxLoad: function()
    {
        this.addError(this.ajaxErrorMessage);
    },

    onCompleteAjaxLoad: function()
    {
        if (this.updateLoader)
            this.updateLoader.hide();
        if (this.createLoader)
            this.createLoader.hide();
        if (this.thumbsLoader)
            this.thumbsLoader.hide();
        if (this.showLoader)
            this.showLoader.hide();
        this.thumbsLoader = null;
        this.loadState = false;
    },

    addField: function(field)
    {
        this.fields.push(field);
        field.parent = this;
        return this;
    },

    thumbs: function (link, thumbs)
    {
        if (this.loadState) {
            return;
        }
        var parentDiv = link.parentElement.parentElement;
        this.thumbsLoader = parentDiv.children[7];
        if (this.thumbsLoader)
            this.thumbsLoader.show();
        this.loadState = true;
        new Ajax.Request(link.href, {
            method:'get',
            onSuccess:this.onSuccessAjaxLoadThumbs.bind(this, link, thumbs),
            onFailure:this.onFailureAjaxLoad.bind(this),
            onComplete:this.onCompleteAjaxLoad.bind(this)
        });
    },

    onSuccessAjaxLoadThumbs: function (link, thumbs)
    {
        var response;
        var transport = arguments[2];
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error) {
            this.addError(response.error_message);
        } else {
            var parentDiv = link.parentElement.parentElement;
            if (thumbs == 'up') {
                parentDiv.children[4].firstChild.innerHTML = parseInt(parentDiv.children[4].firstChild.innerHTML) + 1;
                if (parentDiv.children[4].hasClassName('thumbs-up-0'))
                    parentDiv.children[4].removeClassName('thumbs-up-0');
            }
            parentDiv.children[5].remove();
            parentDiv.children[5].remove();
        }
    },

    addCaptcha: function(captcha, errorMessage)
    {
        this.captcha = captcha;
        this.captchaErrorMessage = errorMessage;
    },

    addCreateLoader: function(createLoaderId)
    {
        this.createLoader = $(createLoaderId);
        return this;
    },

    addUpdateLoader: function(updateLoaderId)
    {
        this.updateLoader = $(updateLoaderId);
        return this;
    },

    reply: function(parentId, parentName)
    {
        this.clearReply();
        this.replyField.value = parentId;
        var div = document.createElement('div');
        div.addClassName('reply-notice');

        var tempMessage = new Template(this.replyMessage);
        div.insert(tempMessage.evaluate({value: parentName}));
        div.insert('<a href="#" onclick="reviews.clearReply(); return false;"><img src="'+this.closeIconUrl+'" alt="'+this.closeIconTitle+'" title="'+this.closeIconTitle+'"/></a>');

        Element.insert($('productreviews-form'), {top: div});

        new Effect.ScrollTo('productreviews-container', {duration:'0.5', offset: -20});
        var detailField = $('detail');
        if (typeof detailField != 'undefined' && detailField)
            detailField.focus();
    },

    addReplyConfig: function (replyFieldId, replyMessage, closeIconUrl, closeIconTitle)
    {
        this.replyField = $(replyFieldId);
        this.replyMessage = replyMessage;
        this.closeIconUrl = closeIconUrl;
        this.closeIconTitle = closeIconTitle;
    },

    clearReply: function()
    {
        /*
         remove old elements
         */
        $$('.reply-notice').each(function(element){
            element.remove();
        });
        this.replyField.value = '';
    },

    showParent: function(link)
    {
        if (this.loadState) {
            return;
        }
        var parentDiv = link.parentElement.parentElement;
        this.showLoader = parentDiv.children[10];
        if (this.showLoader)
            this.showLoader.show();
        this.loadState = true;
        new Ajax.Request(link.href, {
            method:'get',
            onSuccess:this.onSuccessAjaxLoadShowParent.bind(this, link),
            onFailure:this.onFailureAjaxLoad.bind(this),
            onComplete:this.onCompleteAjaxLoad.bind(this)
        });
    },

    onSuccessAjaxLoadShowParent: function(link)
    {
        var response;
        var transport = arguments[1];
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        var dataHtml;
        if (response.error) {
            dataHtml = '<div class="review ui-corner-all">'+response.error_message+'</div>';
        } else {
            dataHtml = this.template.evaluate(response.item);
        }
        var review = link.parentElement.parentElement;
        review.addClassName('child');
        review.insert({before: dataHtml});
        link.hide();
    },

    addError: function(message)
    {
        this.messageManager.show(message, 'error');
    },

    listAfterUpdate: function()
    {
        var hash;
        var reviewObj;
        if (window.location.href.match(/#/) && !this.restrictionForScrollto) {
            hash = window.location.href.split('#')[1];
            reviewObj = $(hash);
            if (reviewObj) {
                new Effect.ScrollTo(hash, {duration:'0.5', offset: -10});
                this.restrictionForScrollto = true;
            } else {
                this.loadState = false;
                this.update();
            }
        }
    }

};

ProductReviews.Field = Class.create();
ProductReviews.Field.prototype = {

    initialize: function (fieldId, emptyText)
    {
        this.field = $(fieldId);
        this.emptyText = emptyText;
        Event.observe(this.field, 'focus', this.focus.bind(this));
        Event.observe(this.field, 'blur', this.blur.bind(this));
        Event.observe(this.field, 'change', this.change.bind(this));
        this.options = arguments[2] ? arguments[2] : {};
        if (this.options.charNumber != 'undefined') {
            Event.observe(this.field, 'keyup', this.checkLength.bind(this));
        }
        this.clear();
        this.blur();
    },

    focus : function()
    {
        if(this.field.value==this.emptyText){
            this.field.value='';
        }
        if (this.options.expand && this.doExpand) {
            var prField = this;
            this.oldWidth = parseInt(this.field.getStyle('width'));
            this.oldHeight = parseInt(this.field.getStyle('height'));
            this.field.setStyle({width: prField.options.width + 'px', height: prField.options.height + 'px'});
            this.doExpand = false;
        }
        if (this.options.captcha && this.showCaptcha) {
            this.options.captcha.show();
            this.showCaptcha = false;
        }
    },

    blur : function()
    {
        if(this.field.value==''){
            this.field.value=this.emptyText;
        }
    },

    change: function()
    {
        this.field.addClassName('validation-passed');
        this.field.removeClassName('validation-failed');
    },

    validate: function()
    {
        if (this.field.value == this.emptyText || this.field.value.empty()) {
            this.field.removeClassName('validation-passed');
            this.field.addClassName('validation-failed');
            new Effect.Shake(this.field, {distance: 5});
            var prField = this;
            setTimeout(function(){prField.field.removeClassName('validation-failed')}, 500);
            return false;
        }
        return true;
    },

    clear: function()
    {
        this.showCaptcha = true;
        this.doExpand = true;
        if (this.oldWidth && this.oldHeight) {
            this.field.setStyle({width: this.oldWidth + 'px', height: this.oldHeight + 'px'});
        }
        this.field.value = '';
        this.blur();
    },

    checkLength: function(e)
    {
        var curNumber = e.target.value.length;
        if (curNumber > this.options.charNumber) {
            e.target.value = e.target.value.slice(0,this.options.charNumber);
            if (typeof this.options.errorMessage != 'undefined') {
                this.parent.addError(this.options.errorMessage);
            }
        }
    }
};

ProductReviews.MessageManager = Class.create();
ProductReviews.MessageManager.prototype = {
    initialize: function(container)
    {
        if (!container) {
            container = 'review-messages-container';
        }
        this.container = container;
        this.errorContainer = '#' + container + ' .error-msg';
        this.successContainer = '#' + container + ' .success-msg';
    },

    show: function(message, type, hide)
    {
        var self = this;
        if (!hide) {
            hide = true;
        }
        switch (type) {
            case 'error':
                if ($$(this.errorContainer)[0] && $$(this.errorContainer)[0].select('span')[0]) {
                    $$(this.errorContainer)[0].select('span')[0].update(message);
                    Effect.Appear($$(this.errorContainer)[0], {duration:.5});
                    new Effect.ScrollTo(this.container, {duration:'0.5', offset: -20});
                    if (hide) {
                        setTimeout(function(){
                            self.hide('error');
                        }, 3000);
                    }
                }
                break;
            case 'success':
                if ($$(this.successContainer)[0] && $$(this.successContainer)[0].select('span')[0]) {
                    $$(this.successContainer)[0].select('span')[0].update(message);
                    Effect.Appear($$(this.successContainer)[0], {duration:.5});
                    new Effect.ScrollTo(this.container, {duration:'0.5', offset: -20});
                    if (hide) {
                        setTimeout(function() {
                            self.hide('success');
                        }, 3000);
                    }
                }
                break;
        }
    },

    hide: function(type)
    {
        if (!type){
            type = 'all';
        }
        if (type == 'error' || type == 'all') {
            if ($$(this.errorContainer)[0] && $$(this.errorContainer)[0].select('span')[0]) {
                $$(this.errorContainer)[0].select('span')[0].update('');
                Effect.Fade($$(this.errorContainer)[0], {duration:.5});
            }
        }
        if (type == 'success' || type == 'all') {
            if ($$(this.successContainer)[0] && $$(this.successContainer)[0].select('span')[0]) {
                $$(this.successContainer)[0].select('span')[0].update('');
                Effect.Fade($$(this.successContainer)[0], {duration:.5});
            }
        }
    }
};

ProductReviews.Captcha = Class.create();
ProductReviews.Captcha.prototype = {

    initialize: function(captcha, elId, publicKey, privateKey, theme, remoteIp, verifyUrl)
    {
        this.captcha = captcha;
        this.eiId = elId;
        this.publicKey = publicKey;
        this.privateKey = privateKey;
        this.theme = theme;
        this.remoteIp = remoteIp;
        this.verifyUrl = verifyUrl;
        this.challenge = null;
        this.response = null;
        this.result = '';
    },

    show: function()
    {
        this.captcha.create(this.publicKey,
            this.eiId,
            {
                theme: this.theme
            }
        );
    },

    verify: function(obj)
    {
        this.challenge = this.captcha.get_challenge();
        this.response = this.captcha.get_response();
        var pair = {};
        pair['privatekey'] = this.privateKey;
        pair['remoteip'] = this.remoteIp;
        pair['challenge'] = this.challenge;
        pair['response'] = this.response;
        var params =  Hash.toQueryString(pair);
        new Ajax.Request(this.verifyUrl, {
            method:'post',
            parameters: params,
            onSuccess:this.onSuccessAjaxLoad.bind(this, obj),
            onFailure:this.onFailureAjaxLoad.bind(this),
            onComplete:this.onCompleteAjaxLoad.bind(this)
        });
    },

    onSuccessAjaxLoad: function(obj)
    {
        var response;
        var transport = arguments[1];
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        this.result = response.result;
        this.message = response.message;
        var res = (this.result == 'true');
        obj.returnValidation(res);
    },

    onFailureAjaxLoad: function()
    {
        //
    },

    onCompleteAjaxLoad: function()
    {
        if (this.result == 'true') {
            this.captcha.destroy();
        }
    },

    reload: function()
    {
        this.captcha.reload();
    }
};

ProductReviews.Rating = Class.create();
ProductReviews.Rating.prototype = {

    initialize: function(containerId, fieldId, formId)
    {
        this.container = $(containerId);
        this.field = $(fieldId);
        this.form = $(formId);
        this.valueTrack = this.container.firstChild;
        this.handler = false;
        this.value = 0;
        Event.observe(this.container, 'click', this.set.bind(this));
        Event.observe(this.container, 'mouseenter', this.start.bind(this));
        Event.observe(this.container, 'mouseleave', this.stop.bind(this));
        Event.observe(this.container, 'mousemove', this.adjust.bind(this));
        Event.observe(this.form, 'form:aftersubmit', this.clean.bind(this));
        if (Prototype.Browser.WebKit) {
            Event.observe(this.container, 'mouseover', this.start.bind(this));
            Event.observe(this.container, 'mouseout', this.stop.bind(this));
        }
    },

    set: function()
    {
        this.field.value = this.value;
        this.handler = false;
    },

    start: function()
    {
        this.handler = true;
    },

    stop: function()
    {
        this.handler = false;
        if (this.value != this.field.value && this.field.value != '') {
            var realX = this.field.value*32;
            Element.setStyle(this.valueTrack, {width: realX + 'px'});
        } else if (this.field.value == '') {
            Element.setStyle(this.valueTrack, {width: 0});
        }
    },

    adjust: function(e)
    {
        if (!this.handler) {
            return;
        }
        var x = e.pageX - this.container.offsetLeft;
        var c = Math.ceil(x/16);
        var realX = c*16;
        this.value = realX/32;
        Element.setStyle(this.valueTrack, {width: realX + 'px'});
    },

    clean: function()
    {
        this.field.value = '';
        Element.setStyle(this.valueTrack, {width: 0});
    }

};

