<div name="syndication_choices" id="syndication_choices" class="syndication_choices" onmouseover="ClearSyndicationTimeout()" onmouseout="HideSyndication(this)">
    <ul>
        <li><a href="{U_FEED}?feed=rss" title="{L_RSS}"><img src="{PATH_TO_ROOT}/templates/{THEME}/framework/content/syndication/images/addrss.png" title="{L_RSS}" alt="{L_RSS}" /></a></li>
        <li><a href="{U_FEED}?feed=atom" title="{L_ATOM}"><img src="{PATH_TO_ROOT}/templates/{THEME}/framework/content/syndication/images/addatom.png" title="{L_ATOM}" alt="{L_ATOM}" /></a></li>
        <li>
            <a href="http://www.netvibes.com/subscribe.php?type=rss&url={U_FEED}?feed=rss" title="">
                <img src="{PATH_TO_ROOT}/templates/{THEME}/framework/content/syndication/images/add2netvibes.png" title="netvibes" alt="Netvibes" />
            </a>
        </li>
    </ul>
</div>