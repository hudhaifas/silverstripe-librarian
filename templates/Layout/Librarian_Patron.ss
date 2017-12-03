<% include Menu_Patron %>

<div class="col-md-10">
    <div class="row">
        <h2>$Title</h2>
        <% if $Email %><p>Email: $Email</p><% end_if %>
        <% if $Phone %><p>Phone: $Phone</p><% end_if %>
        <% if $MaxLoans %><p>Max Loans: $MaxLoans</p><% end_if %>
    </div>

    <% if $Results %>
    <div class="row">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 10%;">Cover</th>
                    <th style="width: 40%;">Book</th>
                    <th>Loan</th>
                    <% if ReturnAction %><th>Action</th><% end_if %>
                </tr>
            </thead>
            <tbody>
                <% loop $Results %>
                <tr>
                    <% with $Book %>
                    <td>
                        <div class="thumbnail text-center related-default">
                            <% if $BookCopy.CoverImage %>
                            <img class="img-responsive" src="$BookCopy.CoverImage.PaddedImage(280, 410).Watermark.URL" />
                            <% else %>
                            <img class="img-responsive" src= "librarian/images/book-cover.jpg" />

                            <div class="caption" style="">
                                <h4>$BookName.LimitCharacters(110)</h4>
                            </div>
                            <% end_if %>
                        </div>
                    </td>
                    <td>
                        <% if $BookName %><b><a href="$Link">$BookName</a></b><% end_if %>
                        <% if $BookTitle && $BookTitle != $BookName %><b>$BookTitle</b><% end_if %>
                        <p><%t Librarian.VOLUME_NUMBER "Volume Number {value}" value=$TheIndex %></p>

                        <% loop Authors %>
                        <a href="$Link" >$FullName</a><% if not $Last %>,<% end_if %>
                        <% end_loop %>


                        <% if $ISBN %><span class="information"><%t Librarian.ISBN 'Subject' %>: $ISBN</span><% end_if %>
                        <% if $Edition || $PublishYear %><span class="information"><%t Librarian.EDITION 'Edition' %>: $Edition ($PublishYear)</span><% end_if %>
                        <% if $Publisher && $Publisher.Name %>
                        <span class="information">
                            <%t Librarian.PUBLISHER 'Publisher' %>: <a title="$Publisher.Name">$Publisher.Title</a>
                        </span>
                        <% end_if %>
                        <% if $Collection %><span class="information"><%t Librarian.COLLECTION 'Collection' %>: $Collection</span><% end_if %>
                        <% if $Format %><span class="information"><%t Librarian.FORMAT 'Format' %>: $Format.Title</span><% end_if %>
                    </td>
                    <% end_with %>

                    <td>
                        <%t Librarian.LoanDate 'Loan Date' %>: <p>$LoanDate</p>
                        <%t Librarian.DueDate 'Due Date' %>: <p>$DueDate</p>
                        <% if $ReturnedBy %><%t Librarian.ReturnDate 'Return Date' %>: <p>$Created</p><% end_if %>
                    </td>

                    <% if Top.ReturnAction %>
                    <td>
                        $Top.ReturnForm($ID)
                    </td>
                    <% end_if %>
                </tr>
                <% end_loop %>
            </tbody>
        </table>
    </div>

    <div class="row">
        <% with $Results %>
        <% include Paginate %>
        <% end_with %>
    </div>
    <% end_if %>

</div>