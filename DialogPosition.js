function setMenuPosition(showid, menuid, pos) {
    var showObj = $(showid);
    var menuObj = menuid ? $(menuid) : $(showid + '_menu');
    if (isUndefined(pos) || !pos)
        pos = '43';
    var basePoint = parseInt(pos.substr(0, 1));
    var direction = parseInt(pos.substr(1, 1));
    var important = pos.indexOf('!') != -1 ? 1 : 0;
    var sxy = 0
      , sx = 0
      , sy = 0
      , sw = 0
      , sh = 0
      , ml = 0
      , mt = 0
      , mw = 0
      , mcw = 0
      , mh = 0
      , mch = 0
      , bpl = 0
      , bpt = 0;
    if (!menuObj || (basePoint > 0 && !showObj))
        return;
    if (showObj) {
        sxy = fetchOffset(showObj);
        sx = sxy['left'];
        sy = sxy['top'];
        sw = showObj.offsetWidth;
        sh = showObj.offsetHeight;
    }
    mw = menuObj.offsetWidth;
    mcw = menuObj.clientWidth;
    mh = menuObj.offsetHeight;
    mch = menuObj.clientHeight;
    switch (basePoint) {
    case 1:
        bpl = sx;
        bpt = sy;
        break;
    case 2:
        bpl = sx + sw;
        bpt = sy;
        break;
    case 3:
        bpl = sx + sw;
        bpt = sy + sh;
        break;
    case 4:
        bpl = sx;
        bpt = sy + sh;
        break;
    }
    switch (direction) {
    case 0:
        menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
        mt = (Math.min(document.documentElement.clientHeight, document.body.clientHeight) - menuObj.clientHeight) / 2;
        break;
    case 1:
        ml = bpl - mw;
        mt = bpt - mh;
        break;
    case 2:
        ml = bpl;
        mt = bpt - mh;
        break;
    case 3:
        ml = bpl;
        mt = bpt;
        break;
    case 4:
        ml = bpl - mw;
        mt = bpt;
        break;
    }
    var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
    var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
    if (!important) {
        if (in_array(direction, [1, 4]) && ml < 0) {
            ml = bpl;
            if (in_array(basePoint, [1, 4]))
                ml += sw;
        } else if (ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
            ml = bpl - mw;
            if (in_array(basePoint, [2, 3])) {
                ml -= sw;
            } else if (basePoint == 4) {
                ml += sw;
            }
        }
        if (in_array(direction, [1, 2]) && mt < 0) {
            mt = bpt;
            if (in_array(basePoint, [1, 2]))
                mt += sh;
        } else if (mt + mh > scrollTop + Math.min(document.documentElement.clientHeight, document.body.clientHeight) && sy >= mh) {
            mt = bpt - mh;
            if (in_array(basePoint, [3, 4]))
                mt -= sh;
        }
    }
    if (pos.substr(0, 3) == '210') {
        ml += 69 - sw / 2;
        mt -= 5;
        if (showObj.tagName == 'TEXTAREA') {
            ml -= sw / 2;
            mt += sh / 2;
        }
    }
    if (direction == 0 || menuObj.scrolly) {
        if (BROWSER.ie && BROWSER.ie < 7) {
            if (direction == 0)
                mt += scrollTop;
        } else {
            if (menuObj.scrolly)
                mt -= scrollTop;
            menuObj.style.position = 'fixed';
        }
    }
    if (ml)
        menuObj.style.left = ml + 'px';
    if (mt)
        menuObj.style.top = mt + 'px';
    if (direction == 0 && BROWSER.ie && !Math.min(document.documentElement.clientHeight, document.body.clientHeight)) {
        menuObj.style.position = 'absolute';
        menuObj.style.top = (Math.min(document.documentElement.clientHeight, document.body.clientHeight) - menuObj.clientHeight) / 2 + 'px';
    }
    if (menuObj.style.clip && !BROWSER.opera) {
        menuObj.style.clip = 'rect(auto, auto, auto, auto)';
    }
}
