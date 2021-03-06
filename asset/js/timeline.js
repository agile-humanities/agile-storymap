var oStorymap = {
    resizeTimerID: null,

    resizeStorymap: function() {
        if (resizeTimerID == null) {
            resizeTimerID = window.setTimeout(function() {
                resizeTimerID = null;
                tl.layout();
            }, 500);
        }
    },

    _monkeyPatchFillInfoBubble: function() {
        var oldFillInfoBubble =
            Storymap.DefaultEventSource.Event.prototype.fillInfoBubble;
        Storymap.DefaultEventSource.Event.prototype.fillInfoBubble =
            function(elmt, theme, labeller) {
            var doc = elmt.ownerDocument;

            var title = this.getText();
            var link = this.getLink();
            var image = this.getImage();

            if (image != null) {
                var img = doc.createElement("img");
                img.src = image;

                theme.event.bubble.imageStyler(img);
                elmt.appendChild(img);
            }

            var divTitle = doc.createElement("div");
            var textTitle = doc.createElement("span");
            textTitle.innerHTML = title;
            if (link != null) {
                var a = doc.createElement("a");
                a.href = link;
                a.appendChild(textTitle);
                divTitle.appendChild(a);
            } else {
                divTitle.appendChild(textTitle);
            }
            theme.event.bubble.titleStyler(divTitle);
            elmt.appendChild(divTitle);

            var divBody = doc.createElement("div");
            this.fillDescription(divBody);
            theme.event.bubble.bodyStyler(divBody);
            elmt.appendChild(divBody);

            var divTime = doc.createElement("div");
            this.fillTime(divTime, labeller);
            theme.event.bubble.timeStyler(divTime);
            elmt.appendChild(divTime);

            var divWiki = doc.createElement("div");
            this.fillWikiInfo(divWiki);
            theme.event.bubble.wikiStyler(divWiki);
            elmt.appendChild(divWiki);
        };
    },

    loadStorymap: function(storymapId, storymapData, params) {
        oStorymap._monkeyPatchFillInfoBubble();
        var eventSource = new Storymap.DefaultEventSource();

        var defaultTheme = Storymap.getDefaultTheme();
        defaultTheme.mouseWheel = 'zoom';
        // defaultTheme.autoWidth = true;

        var bandInfos = [];
        if (typeof params.bandInfos !== 'undefined' && params.bandInfos.length) {
            for (i = 0; i < params.bandInfos.length; ++i) {
                if (typeof params.bandInfos[i].eventSource === 'undefined') {
                    params.bandInfos[i].eventSource = eventSource;
                }
                bandInfos[i] = Storymap.createBandInfo(params.bandInfos[i]);
            }
        } else {
            bandInfos = [
                Storymap.createBandInfo({
                    eventSource: eventSource,
                    width: "80%",
                    intervalUnit: Storymap.DateTime.MONTH,
                    intervalPixels: 100,
                    zoomIndex: 10,
                    zoomSteps: new Array(
                        {pixelsPerInterval: 280, unit: Storymap.DateTime.HOUR},
                        {pixelsPerInterval: 140, unit: Storymap.DateTime.HOUR},
                        {pixelsPerInterval: 70, unit: Storymap.DateTime.HOUR},
                        {pixelsPerInterval: 35, unit: Storymap.DateTime.HOUR},
                        {pixelsPerInterval: 400, unit: Storymap.DateTime.DAY},
                        {pixelsPerInterval: 200, unit: Storymap.DateTime.DAY},
                        {pixelsPerInterval: 100, unit: Storymap.DateTime.DAY},
                        {pixelsPerInterval: 50, unit: Storymap.DateTime.DAY},
                        {pixelsPerInterval: 400, unit: Storymap.DateTime.MONTH},
                        {pixelsPerInterval: 200, unit: Storymap.DateTime.MONTH},
                        {pixelsPerInterval: 100, unit: Storymap.DateTime.MONTH} // DEFAULT zoomIndex
                    )
                }),
                Storymap.createBandInfo({
                    overview: true,
                    eventSource: eventSource,
                    width: "20%",
                    intervalUnit: Storymap.DateTime.YEAR,
                    intervalPixels: 200
                })
            ];
        }

        // All bands are synchronized with the first.
        for (i = 1; i < bandInfos.length; ++i) {
            bandInfos[i].syncWith = 0;
            bandInfos[i].highlight = true;
        }

        var tl = Storymap.create(document.getElementById(storymapId), bandInfos);
        tl.loadJSON(storymapData, function(json, url) {
        // log the storymapData, and see what's there
        // figure out what's creating the storymapData json
        // console.log("json: ", json);
        // console.log("storymapData: ", storymapData);
            if (json.events.length > 0) {
                eventSource.loadJSON(json, url);
                var centerDate = params.centerDate;
                // console.log("centerDate: " + centerDate);
                if (!centerDate) {
                    centerDate = new Date().toJSON().slice(0,10);
                } else if (centerDate === '0000-00-00') {
                    centerDate = eventSource.getEarliestDate();
                } else if (centerDate === '9999-99-99') {
                    centerDate = eventSource.getLatestDate();
                }
                // console.log("centerDate: " + centerDate);
                var parsedDate = Storymap.DateTime.parseGregorianDateTime(centerDate);
                // console.log('parseddate: ', parsedDate);
                tl.getBand(0).setCenterVisibleDate(parsedDate);
            }
        });
    }
};
