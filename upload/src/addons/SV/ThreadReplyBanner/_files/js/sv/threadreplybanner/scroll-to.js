$(document).ready(function()
{
    var focusEditor = XF.focusEditor;
    XF.focusEditor = function ($container, notConstraints)
    {
        var $el = $container.closest('.js-quickReply');
        if ($el.length > 0)
        {
            var height = $(".p-navSticky.is-sticky").height();
            if (height > 0)
            {
                var viewportOffset = $el.get(0).getBoundingClientRect();
                if (viewportOffset.top < height)
                {
                    window.scroll(0, window.scrollY - height + viewportOffset.top - 1);
                }
                return;
            }
        }
        focusEditor($container, notConstraints);
    };
});