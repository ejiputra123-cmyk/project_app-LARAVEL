<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index()
    {
        return view('members.index');
    }

    public function apiData()
    {
        $members = Member::orderBy('id')->get();

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_code' => ['nullable', 'string', 'max:50', 'unique:members,member_code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
        ]);

        if (empty($validated['member_code'])) {
            $lastId = (Member::max('id') ?? 0) + 1;
            $validated['member_code'] = 'MBR-' . str_pad((string) $lastId, 4, '0', STR_PAD_LEFT);
        }

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Member berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'member_code' => ['required', 'string', 'max:50', Rule::unique('members', 'member_code')->ignore($member->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
        ]);

        $member->update($validated);

        return redirect()->route('members.index')->with('success', 'Member berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $member = Member::findOrFail($id);
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus.');
    }
}
