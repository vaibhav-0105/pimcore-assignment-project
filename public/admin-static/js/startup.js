pimcore.registerNS("pimcore.plugin.TestBundle");
pimcore.plugin.TestBundle = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.postSaveObject, this.postSaveObject.bind(this));
        document.addEventListener(pimcore.events.preSaveObject, this.preSaveObject.bind(this));
    },
    
    preSaveObject: function (object, type)
    {
        //alert('hii');
        let color_count = object.detail.object.data.data.color;
        //console.log(color_count);
        let count=color_count.length;
        //console.log(count);
    
        if (count>2) {
            Ext.Msg.alert('Colors should not be more than 2.');
        }
    },
    postSaveObject: function (object, type)
    {
        //alert('hii');
        let color_count = object.detail.object.data.data.color;
        // console.log(color_count);
        let count=color_count.length;
        //console.log(count);
    
        if (count>2) {
            Ext.Msg.alert('Colors should not be more than 2.');
        }
    },
    
    pimcoreReady: function (e) {
    }
});
var TestBundlePlugin = new pimcore.plugin.TestBundle();