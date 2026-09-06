@php
    $url = Route::currentRouteName();
@endphp
<div class="row">

<!-- From Branch Field -->
    <div class="col-md-6 form-group">
        <label for="fromBranch">Move From Branch <span class="text-danger">*</span></label>
        <select name="from_branch_id"  id="fromBranch" class="form-control select2" required>
            <option value="">Select a Branch</option>
            @foreach ($branches as $branche)
            <option value="{{ $branche->id }}" {{ old('from_branch_id') == $branche->id ? 'selected' : '' }}>
                                {{ $branche->name }}
            </option>
            @endforeach
        </select>
        @error('from_branch_id')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- To Branch Field -->
    <div class="col-md-6 form-group">
        <label for="toBranch">Move To Branch <span class="text-danger">*</span></label>
        <select name="to_branch_id"  id="toBranch" class="form-control select2" required>
            <option value="">Select a Branch</option>
            @foreach ($branches as $branche)
            <option value="{{ $branche->id }}" {{ old('to_branch_id') == $branche->id ? 'selected' : '' }}>
                                {{ $branche->name }}
            </option>
            @endforeach
        </select>
        @error('to_branch_id')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label for="parts">Spare Parts <span class="text-danger">*</span></label>
        <select  id="parts" class="form-control select2" required>
            <option value="">Select a Spare Part</option>

        </select>
        @error('parts')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-bordered mt-3" id="partsTable">
                <thead>
                    <tr>
                        <th>Part Name</th>
                        <th>Unit Price</th>
                        <th>Available Qty</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="offset-md-6 col-md-6">
        <table class="table table-bordered">
            <tr>
                <th>Total Quantity</th>
                <td id="totalQty">0</td>
            </tr>
            <tr>
                <th>Grand Total</th>
                <td id="grandTotal">0.00</td>
            </tr>
        </table>
    </div>
</div>

