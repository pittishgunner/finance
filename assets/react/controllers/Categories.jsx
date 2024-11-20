//
// https://mui.com/x/api/tree-view/rich-tree-view/
//

import * as React from 'react';
import Box from '@mui/material/Box';
import { RichTreeViewPro} from '@mui/x-tree-view-pro/RichTreeViewPro';
import { TreeViewBaseItem } from '@mui/x-tree-view/models';
import {useTreeViewApiRef} from "@mui/x-tree-view";
import {Button} from "@mui/material";
import {Stack} from "@mui/system";
import {useEffect, useState} from "react";

export default function LabelEditingAllItems(props) {
    const apiRef = useTreeViewApiRef();
    const [items, setItems] = useState(MUI_X_PRODUCTS);

    useEffect(() => {
        // This will be called for each new value of selectedNode, including the initial empty one
        // Here is where you can make your API call

        console.warn(items)
    }, [items]);

    const handleButtonClick = (event) => {
        apiRef.current?.focusItem(event, 'pickers');
    };
    const handleItemLabelChange = async (itemId, newLabel) => {
        console.warn(itemId);
        console.warn(newLabel);
        const itemTree = apiRef.current.getItemTree();
        console.warn(itemTree);
    };
    const handleItemPositionChange = (params) => {
        console.warn(params);
        const itemTree = apiRef.current.getItemTree();
        console.warn(itemTree);
    }

    const MUI_X_PRODUCTS = [
        {
            id: 'grid',
            label: 'Data Grid',

            children: [
                { id: 'grid-community', label: '@mui/x-data-grid' },
                { id: 'grid-pro', label: '@mui/x-data-grid-pro' },
                { id: 'grid-premium', label: '@mui/x-data-grid-premium' },
            ],
        },
        {
            id: 'pickers',
            label: 'Date and Time pickers',

            children: [
                {
                    id: 'pickers-community',
                    label: '@mui/x-date-pickers',
                },
                { id: 'pickers-pro', label: '@mui/x-date-pickers-pro' },
            ],
        },
        {
            id: 'charts',
            label: 'Charts',

            children: [{ id: 'charts-community', label: '@mui/x-charts' }],
        },
        {
            id: 'tree-view',
            label: 'Tree View',
            children: [{ id: 'tree-view-community', label: '@mui/x-tree-view' }],
        },
    ];

    return (
        <Stack spacing={2}>
            <div>
                <Button onClick={handleButtonClick}>Focus pickers item</Button>
            </div>
            <Box sx={{ minHeight: 352, minWidth: 260 }}>
                <RichTreeViewPro
                    items={MUI_X_PRODUCTS}
                    isItemEditable
                    itemsReordering
                    experimentalFeatures={{
                        indentationAtItemLevel: true,
                        itemsReordering: true,
                        labelEditing: true,
                    }}
                    defaultExpandedItems={['grid', 'pickers']}
                    apiRef={apiRef}
                    onItemLabelChange={handleItemLabelChange}
                    onItemPositionChange={handleItemPositionChange}
                />
            </Box>
        </Stack>
    );
}
