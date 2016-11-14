<form class="form-inline" $AttributesHTML role="search">
    <fieldset>
        <div class="form-group">
            <!-- Hidden input-->
            <div class="form-hidden">
                <input id="{$FormName}_SecurityID" name="SecurityID" type="hidden" value="{$SecurityID}" />
            </div>

            <label class="sr-only" for="{$FormName}_SearchTerm"><%t Librarian.SEARCH 'Search' %></label>

            <div class="input-group">
<!--                                <div class="input-group-btn">
                                    <select id="lunch" class="selectpicker btn btn-default" data-live-search="true" title="" style="">
                                        <option>Title</option>
                                        <option>Author</option>
                                        <option>All</option>
                                    </select>
                                </div>-->

                <input type="text" class="form-control" placeholder="<%t Librarian.SEARCH 'Search' %>" name="{$FormName}_SearchTerm" id="{$FormName}_SearchTerm" />

                <div class="input-group-btn">
                    <button id="{$FormName}_action_doSearchBook" name="action_doSearchBook" class="btn btn-default" type="submit">
                        <i class="glyphicon glyphicon-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </fieldset>
</form>