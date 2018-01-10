<table class="table">
    <tbody>
        <tr>
            <th><%t Librarian.PATRON 'Patron' %></th>
            <% with $Patron %>
            <td>
                <% if $Title %><b><a href="$ObjectLink">$Title</a></b><% end_if %>
                <% if $Email %><p><%t Librarian.EMAIL 'Email' %>: $Email</p><% end_if %>
                <% if $Phone %><p><%t Librarian.PHONE 'Phone' %>: $Phone</p><% end_if %>
            </td>
            <% end_with %>
        </tr>

        <tr>
            <th><%t Librarian.BOOK 'Book' %></th>
            <% with $Book %>
                <td>
                    <% if $BookName %><a href="$ObjectLink">$BookName</a><% end_if %>
                    <% if $BookTitle && $BookTitle != $BookName %>$BookTitle<% end_if %>
                </td>
            <% end_with %>
        </tr>

        <tr>
            <th><%t Librarian.VOLUME 'Volume' %></th>
            <% with $Book %>
                <td>
                    $TheIndex
                </td>
            <% end_with %>
        </tr>

        <tr>
            <th><%t Librarian.LOAN_DATE 'Loan Date' %></th>
            <td>$LoanDate</td>
        </tr>

        <tr>
            <th><%t Librarian.DUE_DATE 'Due Date' %></th>
            <td>$DueDate</td>
        </tr>

        <tr>
            <th><%t Librarian.RETURN_DATE 'Return Date' %></th>
            <td>$Created</td>
        </tr>

        <tr>
            <th></th>
            <td></td>
        </tr>

        <% with $Book %>
            <tr>
                <th><%t Librarian.AUTHORS 'Authors' %></th>
                <td>
                    <% loop Authors %>
                        <a href="$ObjectLink" >$FullName</a><% if not $Last %>, <% end_if %>
                    <% end_loop %>
                </td>
            </tr>

            <% if $Publisher && $Publisher.Name %>
            <tr>
                <th><%t Librarian.PUBLISHER 'Publisher' %></th>
                <td><a href="$Publisher.ObjectLink" title="$Publisher.Name">$Publisher.Title</a></td>
            </tr>
            <% end_if %>

            <% if $ISBN %>
            <tr>
                <th><%t Librarian.ISBN 'ISBN' %></th>
                <td>$ISBN</td>
            </tr>
            <% end_if %>

            <% if $Edition || $PublishYear %>
            <tr>
                <th><%t Librarian.EDITION 'Edition' %></th>
                <td>$Edition ($PublishYear)</td>
            </tr>
            <% end_if %>

            <% if $Collection %>
            <tr>
                <th><%t Librarian.COLLECTION 'Collection' %></th>
                <td>$Collection</td>
            </tr>
            <% end_if %>

            <% if $Format %>
            <tr>
                <th><%t Librarian.FORMAT 'Format' %></th>
                <td><a href="$Format.ObjectLink">$Format.Title</a></td>
            </tr>
            <% end_if %>
        <% end_with %>

        <tr>
            <th></th>
            <td></td>
        </tr>

    </tbody>
</table>