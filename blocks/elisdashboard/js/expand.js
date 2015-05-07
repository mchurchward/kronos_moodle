/**
 * Expland an elis dashboard widget.
 *
 * @param int id Instance id of widget.
 */
function block_elisdashboard_expand(id) {
    var wrapperid = 'inst' + id,
        activeitem = false,
        isdocked = false,
        ele = document.getElementById(wrapperid),
        screenheight = window.innerHeight,
        screenwidth = window.innerWidth,
        boxheight,
        boxwidth,
        height,
        width;
    if (typeof M.core.dock.get === "undefined") {
        // Expand block once module is loaded.
        Y.use('moodle-core-dock', function () {
            M.core.dock.init();
            block_elisdashboard_expand(id);
        });
        return;
    }
    activeitem = M.core.dock.get().getActiveItem();
    if (typeof activeitem == "object" && activeitem !== false) {
        isdocked = activeitem.get('block').get('isDocked');
        if (isdocked) {
            activeitem.get('block').set('expandisdocked', true);
            activeitem.hide();
            // Return the block to its original position.
            block_elisdashboard_returntopage(activeitem.get('block'));

            // Remove the dock item node.
            activeitem.get('dockItemNode').remove();
            ele.parentElement.style.display = 'block';
        } else {
            activeitem.set('expandisdocked', false);
        }
    }
    var offsettop = ele.offsetTop;
    ele.className = ele.className + ' block_elisdashboard_expanded';

    boxheight = ele.getBoundingClientRect().height;
    boxwidth = ele.getBoundingClientRect().width;

    height = screenheight - boxheight;
    if (height < 0) {
        ele.style.top = '0px';
    } else {
        ele.style.top = height / 2 + 'px';
    }
    width = screenwidth - boxwidth;
    if (width < 0) {
        ele.style.left = '0px';
    } else {
        ele.style.left = width / 2 + 'px';
    }
}

/**
 * Undock an elis dashboard widget to the main page to be expanded but do not save state as undock.
 *
 * @param object block Block to undock.
 */
function block_elisdashboard_returntopage(block) {
    var commands;

    // Enable the skip anchor when going back to block mode
    if (block.contentskipanchor) {
        block.contentskipanchor.show();
    }

    if (block.cachedcontentnode.one('.header')) {
        block.cachedcontentnode.one('.header').insert(block.dockitem.get('contents'), 'after');
    } else {
        block.cachedcontentnode.insert(block.dockitem.get('contents'));
    }

    block.contentplaceholder.replace(block.cachedcontentnode);
    block.cachedcontentnode = Y.one('#' + block.cachedcontentnode.get('id'));

    commands = block.dockitem.get('commands');
    if (commands) {
        commands.all('.hidepanelicon').remove();
        commands.all('.moveto').remove();
        commands.remove();
    }
    block.cachedcontentnode = null;
    block.set('isDocked', false);
}

/**
 * Unexpland an elis dashboard widget.
 *
 * @param int id Instance id of widget.
 */
function block_elisdashboard_unexpand(id) {
    var wrapperid = 'inst' + id,
        activeitem = false;
    var isdocked = false;
    if (typeof M.core.dock._dockableblocks[id] !== "undefined") {
        activeitem = M.core.dock._dockableblocks[id];
    }
    if (typeof activeitem == "object" && activeitem !== false) {
        isdocked = activeitem.get('expandisdocked');
        if (isdocked) {
            activeitem.moveToDock();
        }
        activeitem.set('expandisdocked', false);
    }
    var ele = document.getElementById(wrapperid);
    ele.className = ele.className.replace('block_elisdashboard_expanded', '');
    ele.style.top = '';
}