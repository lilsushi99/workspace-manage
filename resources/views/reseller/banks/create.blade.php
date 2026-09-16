<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bank Account - Dowa Reseller</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); padding: 2rem; }
        .container { max-width: 550px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; margin-top: 1rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; }
        .form-control { width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }
        .btn { padding: 0.65rem 1.5rem; border-radius: 6px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">Add & Verify Bank Account</h1>

        <div class="card">
            <form method="POST" action="{{ route('reseller.banks.store') }}">
                @csrf

                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="e.g. GTBank, Zenith Bank, Chase" value="{{ old('bank_name') }}" required>
                </div>

                <div class="form-group">
                    <label>Bank Code</label>
                    <input type="text" name="bank_code" class="form-control" placeholder="e.g. 058 or DUMMY" value="{{ old('bank_code', '058') }}" required>
                </div>

                <div class="form-group">
                    <label>Account Number</label>
                    <input type="text" name="account_number" class="form-control" placeholder="e.g. 0123456789" value="{{ old('account_number') }}" required>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                    <a href="{{ route('reseller.banks.index') }}" class="btn btn-secondary">&larr; Cancel</a>
                    <button type="submit" class="btn btn-primary">Verify Account & Add &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
