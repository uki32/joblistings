<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    //Show all listings
    function index() {
        return view('listings.index', [
            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)
        ]);
    }

    //Show single listing
    function show(Listing $listing){
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    //Show Create Form
    function create(){
        return view('listings.create');
    }

    //Store Listing Data
    function store(Request $request){
        $formFields = $request->validate([
            'title'=>'required',
            'company'=>['required', Rule::unique('listings', 'company')],
            'location'=>'required',
            'website'=>'required',
            'email'=>['required', 'email'],
            'tags'=>'required',
            'description'=>'required'
        ]);

        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }
        // $user_id = auth()->id();
        // dd($user_id); // Dump and die to inspect the value
        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing created successfully!');
    }

    //Show Edit Form
    function edit(Listing $listing){
        return view('listings.edit', ['listing' => $listing]);
    }
    
    // Update Listing Data
    function update(Request $request, Listing $listing){
        //Make Sure logged in user i owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action!');
        }
        $formFields = $request->validate([
            'title'=>'required',
            'company'=>['required'],
            'location'=>'required',
            'website'=>'required',
            'email'=>['required', 'email'],
            'tags'=>'required',
            'description'=>'required'
        ]);

        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }
 
        $listing->update($formFields);

        return back()->with('message', 'Listing updated successfully!');
    }

    //Delete Listing
    function destroy(Listing $listing){
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action!');
        }
        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted successfully');
    }

    //Manage Listings
   public function manage(){
        return view('listings.manage', ['listings'=> auth()->user()->listings()->get()]);
    }

}
