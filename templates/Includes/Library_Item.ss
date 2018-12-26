<div style="height: auto;">
    <a <% if not $isObjectDisabled %>href="$ObjectLink"<% end_if %> title="$ObjectTitle">
        <div class="card text-center col-sm-12 dataobject-image">
            <% include List_Image %>

            <% if not $isObjectDisabled %>
                <div class="mask"></div>
            <% end_if %>
        </div>

        <div class="content col-sm-12 ellipsis ellipsis-3">
            <p class="title">
                <a <% if not $isObjectDisabled %>href="$ObjectLink"<% end_if %> title="$ObjectTitle">$Title</a>
            </p>
        </div>		
    </a>
</div>