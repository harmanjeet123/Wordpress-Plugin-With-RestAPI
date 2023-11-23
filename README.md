# Inmolink Plugin
Set of shortcodes to display InmoLink property results.

## Sample searchform code:
    [inmolink_property_search_form action="/results-page/" class="" ]
        [inmolink_property_search_field field="ref_no" label="Reference"]
        [inmolink_property_search_field field="location" label="Location"]
        [inmolink_property_search_field field="type" label="Type"]
        [inmolink_property_search_field field="bedrooms_min" data="1,2,3,4,5,6,7,8,9,10" label="Bedrooms"]
        [inmolink_property_search_field field="bathrooms_min" data="1,2,3,4,5,6,7,8,9,10" label="Bathrooms"]
        [inmolink_property_search_field field="list_price_max" label="Max Price" data="100000,150000,200000,250000,300000,350000"]
	    [inmolink_property_search_field field="submit" label="Search" ]
    [/inmolink_property_search_form]

## Sample results code:
    [inmolink_properties perpage=6]
	    [inmolink_property_permalink]
            [inmolink_property_result_field field="slider"]
        [/inmolink_property_permalink]

        [inmolink_shortlist_button add="save" remove="remove"]

        [inmolink_property_permalink class=""]
            [inmolink_property_result_field field="location"]
        [/inmolink_property_permalink]

        [inmolink_property_result_field field="desc" maxlength="120" format="%s ..." class="description" ]
        
        [inmolink_property_result_field field="bedrooms"]

        [inmolink_property_result_field field="bathrooms"]
        
        [inmolink_property_result_field field="build_size"]
        
        [inmolink_property_result_field field="terrace_size"]
        
        [inmolink_property_result_field field="list_price" format="€%s"]
	[/inmolink_properties]

# Shortcodes
## [inmolink_properties]
Shortcode to contain the single property result element. The content nested withing this shortcode will be repeated the same amount of times as the number of results to be displayed on the results page.

The search results will be queried based on the shortcode attributes.

### Attributes
| attribute     | default       | description  |
| ------------- |:-------------:| ----- |
| ref_no | _empty_ | Property reference |
| bedrooms_min | _empty_ | minimum number of bedrooms |
| bedrooms_max | _empty_ | maximum number of bedroos |
| bathrooms_max | _empty_ | minimum number of bathrooms |
| bathrooms_min | _empty_ | maximum number of bathrooms |
| list_price_min | _empty_ | minimum price |
| list_price_max | _empty_ | maximum price |
| order | _empty_ | order api parameter: list_price_asc, list_price_desc, etc |
| ownfirst | _empty_ | set to 1 to display own property firts |
| ownonly | _empty_ | set to 1 to only display own properties |
| page | 1 | page to display |
| types | _empty_ | property types (use comma-separated slugs) |
| locations | _empty_ | property location (use comma-separated slugs) |
| features | _empty_ | property features (use comma-separated slugs) |
| pagination_class | _empty_ | html class of pagination element |
| perpage | 12 | number of properties to display per page |
| shortlist | _empty_ | enter any character to return shortlisted properties |

## [inmolink_noresults]
Shortcode containing content to be displayed when no results are found. It should contain the same attributes as the [inmolink_properties] shortcode for it to work correctly.

## [property_field]
Displays single property detail. This shortcode must be nested inside a `inmolink_properties` or a `inmolink_property` shorcode.

### Attributes
| attribute     | default       | description  |
| ------------- |:-------------:| ----- |
| field | _empty_ | Property field or API field to display. |
| format | %s | Enter a formatting string to add prefixes or suffixes to the returned data. |
| maxlength | _empty_ | Maximum characters to return. |
| thousands | _empty_ | If the field is numeric, this attribute lets you define a thousands separator. |
| separator | " - " | If the field contains a range of values of values, this separator will be used. |
| from | "From " | If the field contains a _starting at_ value, this string will be prepended. |
| per_month | " / Month" | Suffix added to property field if property is a monthly rental.  |
| per_week | " / Week" | Suffix added to property field if property is a weekly rental. |
| class | _empty_ | CSS class |

### Special property fields
| field | description | 
| - | - |
| slider | Slider to display property images in results. |
| image | Display property main image. |
| video | Displays an iframe with the property video link. Vimeo and YouTube links will be converted into embed URLs. |
| virtualtour | Displays an iframe with the property virtualtour link. Vimeo and YouTube links will be converted into embed URLs. |
| pdf | Display link to download a PDF windowcard. |

## [inmolink_shortlist_button]
Displays a button which lets you add or remove properties to a shortlist. Properties will be saved into a cookie named "shortlist" as a comma separated list of property references.

**Note** : Create an html element with `class="shortlist_counter"` and it will automatically display the number of currently shortlisted properties.

### Attributes
| attribute     | default       | description  |
| ------------- |:-------------:| ----- |
| class | _empty_ | CSS class |
| add | + | Label that will display when property is not shortlisted. |
| remove | - | Label that will display when property is shortlisted. |

## [inmolink_property_search_field]
Shortcode to display single search field.
### Attributes
| attribute     | default       | description  |
| ------------- |:-------------:| ----- |
| field | _empty_ | Search field type to display. |
| label | _empty_ | Label or placeholder. |
| slug | _empty_ | Slug of feature for checkbox field. |
| parent | _empty_ | Slug of parent term for hierarchical taxonomy fields. |
| data | _empty_ | Options to populate dropdown fields. Accepts comma separated list of values. Each value can cotain just one value or a key\|value pair separating them with a pipe ('\|') character. |
| data_{a}__{b} | _empty_ | Modifier options with the same format as `data`. This data will be used when another search element `a` is equal to `b`. Can be used to provide different price options for sale and for rent. For exameple: `data_listing_type__long_rental`. |
| thousands | , | Thousands separator to display prices. |
| class | _empty_ | CSS class |

### Field types
| field | description | 
| - | - |
| ref_no | Input field to search by reference |
| listing_type | Dropdown to select sale, rental, etc. Use in conjuction with `data` attribute to provide comma separated list of values. |
| location | Dropdown to select location. |
| type | Dropdown to select property type. |
| feature | Checkbox to filter specific features. |
| bedrooms_min | Dropdown to select minimum number of bedrooms. Use together with `data` attribute to provide comma separated list of numbers. |
| bathrooms_min | Dropdown to select minimum number of bathrooms. Use together with `data` attribute to provide comma separated list of numbers. |
| list_price_min | Dropdown to select minimum price. Use together with `data` attribute to provide comma separated list of numbers. |
| list_price_max | Dropdown to select maxium price. Use together with `data` attribute to provide comma separated list of numbers. |
| reset | Reset button. |
| submit | Search button. |

