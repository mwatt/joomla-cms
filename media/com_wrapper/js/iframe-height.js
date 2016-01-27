function iFrameHeight()
{
    if (window.frames && window.frames['blockrandom'])
    {
        var doc = window.frames['blockrandom'].contentDocument || window.frames['blockrandom'].document || window.frames['blockrandom'].contentWindow.document;
        if (doc !== undefined) {
            document.getElementById('blockrandom').style.height = parseInt(doc.body.scrollHeight) + 20 + 'px';
        }
    }
}
