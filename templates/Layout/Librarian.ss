<% include Menu_Admin %>

<div class="col-md-9">
    <div class="row">
        <h2><%t Librarian.LEND_BOOK 'Lend Book' %></h2>

        <div>$LendForm</div>
    </div>

    <div class="row">
        <h2><%t Librarian.LOAN_ARCHIVE 'Archive' %></h2>
        <% loop $LoansArhive %>

        $Title, $LoanDate <br />
        <% end_loop %>
    </div>

    <div class="row">
        <h2><%t Librarian.OVERDUE 'Overdue' %></h2>
        <% loop $OverDueLoans %>

        $Title, $LoanDate <br />
        <% end_loop %>
    </div>
</div>