@extends('statamic::layout')

@section('content')

    <h1 class="mb-3">Revisions Prototype</h1>
    <div class="card w-1/2 p-0">
        <div>
            <h2 class="p-3">Entry History</h2>
            <h6 class="px-3 pb-1 mb-1 border-b">Today</h6>
            <div class="revisions-listing text-sm pb-2">
                <div class="py-1 px-3 hover:bg-grey-20 block group">
                    <div class="flex items-center text-blue">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status bg-blue mr-1"></i>
                        </span>
                        <span>10:25 AM &mdash; Jack McDade</span>
                        <span class="badge bg-blue uppercase text-white text-4xs ml-1">Working Copy</span>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block group">
                    <div class="flex items-center text-green">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-live mr-1"></i>
                        </span>
                        <span>09:47 AM &mdash; Jack McDade</span>
                        <span class="badge bg-green-light uppercase text-white text-4xs ml-1">Published</span>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>08:35 AM &mdash; Jack McDade</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>08:34 AM &mdash; André Basse</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>08:31 AM &mdash; André Basse</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>08:26 AM &mdash; Jack McDade</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
            </div>

            <h6 class="px-3 pb-1 pt-2 mb-1 border-b">March 19, 2019</h6>
            <div class="revisions-listing text-sm pb-2">
                <div class="py-1 px-3 hover:bg-grey-20 flex items-center text-green block group">
                    <div class="flex items-center justify-between cursor-pointer enter">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-live mr-1"></i>
                        </span>
                        <span>10:45 PM &mdash; Jack McDade</span>
                        <span class="badge bg-green-light uppercase text-white text-4xs ml-1">Published</span>
                    </div>
                    <div class="ml-2">
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>09:47 PM &mdash; Jack McDade</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>04:40 PM &mdash; Jack McDade</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
                <div class="py-1 px-3 hover:bg-grey-20 block flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center">
                        <span class="w-6 text-center pb-sm">
                            <i class="icon-status icon-status-draft mr-1"></i>
                        </span>
                        <span>01:18 PM &mdash; Jack McDade</span>
                    </div>
                    <div>
                        <a href="" class="opacity-0 group-hover:opacity-100 text-grey hover:text-blue text-xs">Preview Version</a>
                        <a href="" class="opacity-0 group-hover:opacity-100 ml-2 text-grey hover:text-blue text-xs">Restore Version</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop
@section('nontent')

    <collection-wizard
        :steps="['Naming', 'Ordering', 'Behavior', 'Content Model', 'Front-End']">
    </collection-wizard>

@stop

@section('nontent')

    <div class="flex mb-5">
        <h1>{{ __('The Statamic Playground') }}</h1>
    </div>

    <h2 class="mb-1">
        Form Inputs
    </h2>

    <div class="shadow bg-white p-4 rounded-lg mb-6">
        <div class="mb-2">
            <input type="text" placeholder="unstyled">
        </div>
        <div class="mb-2">
            <input type="text" class="form-control" placeholder="v2 style">
        </div>
        <div class="mb-2 flex">
            <input type="text" class="input-text" placeholder="v3 style">
            <select class="ml-1" name="" id="">
                <option value="">Oh hai Mark</option>
            </select>
        </div>
        <div class="mb-2 flex">
            <input type="text" class="input-text" placeholder="v3 style">
            <button class="btn ml-1">Default Button</button>
            <button class="btn-primary ml-1">Primary Button</button>
        </div>
        <div class="mb-2">
            <textarea name="" class="input-text" placeholder="v3 style"></textarea>
        </div>
        <div class="mb-2">
            <div class="select-input-container w-64">
                <select class="select-input">
                    <option value="">Oh hai Mark</option>
                    <option value="">I did not do it i did not</option>
                </select>
                <div class="select-input-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                 </div>
            </div>
        </div>
    </div>


    <h2 class="mb-1">Typography</h2>
    <div class="shadow bg-white p-4 rounded-lg overflow-hidden mb-6">
        <h1 class="mb-2">This is first level heading</h1>
        <h2 class="mb-2">This is a second level heading</h2>
        <h3 class="mb-2">This is a third level heading</h3>
        <h4 class="mb-2">This is a fourth level heading</h4>
        <h5 class="mb-2">This is a fifth level heading</h5>
        <h6 class="mb-2">This is a sixth level heading</h6>
        <p>Paragraph text. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam error tempore veritatis, laborum, et assumenda? Necessitatibus excepturi enim quidem maxime! Temporibus dolorum fugit aspernatur.
    </div>

    <h2 class="mb-1">Buttons</h2>
    <div class="shadow bg-white p-4 rounded-lg overflow-hidden mb-6">
        <h6 class="mb-2">Flavors</h6>
        <div class="mb-4">
            <button class="btn mr-2">Default Button</button>
            <button class="btn btn-primary mr-2">Primary Button</button>
            <button class="btn btn-danger mr-2">Danger Button</button>
        </div>
        <h6 class="mb-2">Disabled States</h6>
        <div class="mb-4">
            <button disabled class="btn disabled mr-2">Default Button</button>
            <button disabled class="btn btn-primary disabled mr-2">Primary Button</button>
            <button disabled class="btn btn-danger disabled mr-2">Danger Button</button>
        </div>

        <h6 class="mb-2">Small Variation</h6>
        <div class="mb-4">
            <button class="btn btn-sm mr-2">Default Button</button>
            <button class="btn btn-primary btn-sm mr-2">Primary Button</button>
            <button class="btn btn-danger btn-sm mr-2">Danger Button</button>
        </div>

        <h6 class="mb-2">Large Variation</h6>
        <div>
            <button class="btn btn-lg mr-2">Default Button</button>
            <button class="btn btn-primary btn-lg mr-2">Primary Button</button>
            <button class="btn btn-danger btn-lg mr-2">Danger Button</button>
        </div>
    </div>

    <h2 class="mb-1">Colors</h2>
    <div class="bg-white p-5 shadow rounded-lg overflow-hidden mb-6">

        <h6 class="mb-2">Greys</h6>
        <div class="flex flex-row-reverse text-sm text-center mb-4">
            <div class="text-black bg-white p-2 flex-1">White</div>
            <div class="text-black bg-grey-10 p-2 flex-1">10</div>
            <div class="text-black bg-grey-20 p-2 flex-1">20</div>
            <div class="text-black bg-grey-30 p-2 flex-1">30</div>
            <div class="text-black bg-grey-40 p-2 flex-1">40</div>
            <div class="text-black bg-grey-50 p-2 flex-1">50</div>
            <div class="text-black bg-grey-60 p-2 flex-1">60</div>
            <div class="text-black bg-grey-70 p-2 flex-1">70</div>
            <div class="text-white bg-grey-80 p-2 flex-1">80</div>
            <div class="text-white bg-grey-90 p-2 flex-1">90</div>
            <div class="text-white bg-grey-100 p-2 flex-1">100</div>
            <div class="text-white bg-black p-2 flex-1">Black</div>
        </div>

        <h6 class="mb-2">Other Colors (needs simplifying)</h6>
        <div class="flex text-sm text-center">
            <div class="text-black bg-blue p-3 flex-1">Blue</div>
            <div class="text-black bg-green p-3 flex-1">Green</div>
            <div class="text-black bg-red p-3 flex-1">Red</div>
            <div class="text-black bg-yellow p-3 flex-1">Yellow</div>
            <div class="text-black bg-yellow-dark p-3 flex-1">Yellow Dark</div>
            <div class="text-black bg-pink p-3 flex-1">Pink</div>
            <div class="text-black bg-purple p-3 flex-1">Purple</div>
        </div>
    </div>

    <h2 class="mb-1">Widgets</h2>
    <div class="flex flex-wrap -mx-2 mb-4">
        <div class="w-1/3 px-2">
            <div class="card px-3">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-grey">New Users</h3>
                    <select class="text-xs" name="" id="">
                        <option value="">30 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-2">89</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green mr-1">@svg('performance-increase')</span>
                    <span class="leading-none text-sm">8.54% Increase</span>
                </div>
            </div>
        </div>
        <div class="w-1/3 px-2">
            <div class="card px-3">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-grey">Form Submissions</h3>
                    <select class="text-xs" name="" id="">
                        <option value="">7 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-2">35</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green mr-1">@svg('performance-increase')</span>
                    <span class="leading-none text-sm">2.15% Increase</span>
                </div>
            </div>
        </div>
        <div class="w-1/3 px-2">
            <div class="card bg-grey-90 px-3">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-grey-40">New Users</h3>
                    <select class="text-xs" name="" id="" class="bg-grey-80 border-grey-80 text-grey-40">
                        <option value="">30 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-2 text-grey-40">251</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green mr-1">@svg('performance-increase')</span>
                    <span class="leading-none text-grey-40 text-sm">8.54% Increase</span>
                </div>
            </div>
        </div>
    </div>
@stop
