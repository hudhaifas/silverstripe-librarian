<form class="form-inline" $AttributesHTML>
    <fieldset>
        <!-- Hidden input-->
        <div class="form-hidden">
            <input id="{$FormName}_SecurityID" name="SecurityID" type="hidden" value="{$SecurityID}" />
        </div>

        <div class="form-group">
            <label class="sr-only" for="SerialNumber"><%t Librarian.SERIAL_NUMBER 'SerialNumber' %></label>
            <input id="{$FormName}_SerialNumber" name="SerialNumber" type="text" placeholder="<%t Librarian.SERIAL_NUMBER 'Serial Number' %>" class="form-control" />
        </div>

        <div class="form-group">
            <label class="sr-only" for="BookTitle"><%t Librarian.BOOK_TITLE 'BookTitle' %></label>
            <input id="{$FormName}_BookTitle" name="BookTitle" type="text" placeholder="<%t Librarian.BOOK_TITLE 'BookTitle' %>" class="form-control" />
        </div>

        <!-- Button -->
        <div class="form-group">
            <button id="{$FormName}_action_doSearchCatalog" name="action_doSearchCatalog" class="btn btn-primary" style="height: 34px; line-height: 17px;">
                <%t Librarian.SEARCH 'Search' %>
            </button>
        </div>   
    </fieldset>
</form>
