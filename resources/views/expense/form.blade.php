@php
    $url = \Route::currentRouteName();
@endphp

<div class="row">
    <div class="col-md-6 form-group">
        <label for="expense_type_id">Expense Type <span class="text-danger">*</span></label>
        <select name="expense_type_id" id="expenseType" class="form-control select2">
            <option value="">Select an Expense Type</option>
            @foreach ($expenseTypes as $expenseType)
                <option value="{{ $expenseType->id }}"
                    {{ (old('expense_type_id') == $expenseType->id || ($url == 'expense.edit' && $expense->expense_type_id == $expenseType->id)) ? 'selected' : '' }}>
                    {{ $expenseType->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 form-group">
        <label for="title">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="title" id="title" required
        value="{{$url == 'expense.edit' ? $expense->title : old('title')}}">
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="expense_date">Date <span class="text-danger">*</span></label>
            <div class='input-group date datetime' id=''>
                <input type='text' class="form-control" name="expense_date"
                       value="{{ old('expense_date', $url == 'expense.edit' ? $expense->expense_date : today()) }}" />
                <div class="input-group-append">
                    <span class="input-group-text">
                        <span class="fa fa-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 form-group">
        <label for="amount">Amount <span class="text-danger">*</span></label>
        <input type="number" name="amount" id="amount" class="form-control phone"
               value="{{ old('amount', $url == 'expense.edit' ? $expense->amount : '') }}">
        <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
    </div>


    <div class="col-md-12 form-group">
        <fieldset class="form-group">
            <label for="note">Note</label>
            <textarea class="form-control" id="basicTextarea" name="note" rows="3">{{ old('note', $url == 'expense.edit' ? $expense->note : '') }}</textarea>
        </fieldset>
    </div>
</div>
