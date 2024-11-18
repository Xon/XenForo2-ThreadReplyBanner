var SV = window.SV || {};
SV.$ = SV.$ || window.jQuery || null;

((window, document) =>
{
    'use strict';
    var $ = SV.$, xf22 = typeof XF.on !== "function";

    let focusEditor = XF.focusEditor;

    /**
     * @param {HTMLElement|jQuery} $container
     * @param {boolean} notConstraints
     */
    XF.focusEditor = ($container, notConstraints) =>
    {
        /** @type HTMLElement */
        const container = xf22 ? $container.get(0) : $container;

        const el = container.closest('.js-quickReply');
        if (el)
        {
            const stickyNav = document.querySelector('.p-navSticky.is-sticky');
            if (stickyNav)
            {
                let height = stickyNav
                    .getBoundingClientRect()
                    .height
                ;
                if (height > 0)
                {
                    const viewportOffset = el.getBoundingClientRect();
                    if (viewportOffset.top < height)
                    {
                        window.scroll(0, window.scrollY - height + viewportOffset.top - 1);
                    }
                    return;
                }
            }
        }
        focusEditor($container, notConstraints);
    };
})(window, document)