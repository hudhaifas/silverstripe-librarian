<p class="dataobject-title">$Title</p>

<% if $LoanDate %><p class="dataobject-info"><%t Librarian.LOAN_DATE 'Loan Date' %>: $LoanDate</p><% end_if %>
<% if $DueDate %><p class="dataobject-info"><%t Librarian.DUE_DATE 'Due Date' %>: $DueDate</p><% end_if %>
<% if $ReturnedBy %><p class="dataobject-info"><%t Librarian.RETURN_DATE 'Return Date' %>: $Created</p><% end_if %>
