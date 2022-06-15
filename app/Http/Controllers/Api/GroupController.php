<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Group;
use App\Models\GroupDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::all();
        $groups->load('group_detail');

        return $this->sendResponse($groups, 'Group retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'rcc' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $group = Group::create($input);

        // Save group detail
        if ($request->groups)
            $group->group_detail()->createMany($request->groups);

        if ($group) {
            return $this->sendResponse($group, 'Group added successfully.');
        } else {
            return $this->sendError('Failed to add group.', ['error' => 'Failed to add group.'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Group::with('group_detail')->find($id);

        if (is_null($group)) {
            return $this->sendError('Group not found.');
        }

        return $this->sendResponse($group, 'Group retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $group = Group::findOrFail($id);

        $group->name = $input['name'];
        $group->save();

        return $this->sendResponse($group, 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        return $this->sendResponse($group, 'Group can\'t be deleted.');
    }

    /**
     * Add group child
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function addDetail(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'group_id' => 'required',
            'card_code' => 'required|unique:group_details',
            'card_name' => 'required',
            'currency' => 'required',
            'cust_bu' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (!Group::where('id', $request->group_id)->exists()) {
            return $this->sendError('Add child group failed.', 'Parent group not found.', Response::HTTP_NOT_FOUND);
        }

        $groupDetail = GroupDetail::create($input);

        if ($groupDetail) {
            return $this->sendResponse($groupDetail, 'Group added successfully.');
        } else {
            return $this->sendError('Failed to add group.', ['error' => 'Failed to add group.'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateDetail(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'card_name' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $group = GroupDetail::where('card_code', $id)->firstOrFail();

        $group->card_name = $input['card_name'];
        $group->save();

        return $this->sendResponse($group, 'Group detail updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyDetail($id)
    {
        $groupDetail = GroupDetail::findOrFail($id);
        $groupDetail->delete();

        return $this->sendResponse($groupDetail, 'Group successfully deleted.');
    }
}
